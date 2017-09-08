<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2017 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2017 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    // debug
    if ( sfConfig::get('sf_web_debug', false) && $request->hasParameter('debug') )
    {
      $this->getResponse()->setContentType('text/html');
      $this->setLayout('nude');
    }
    else
      sfConfig::set('sf_web_debug', false);
    
    // I18N helper
    $this->getContext()->getConfiguration()->loadHelpers(array('CrossAppLink','I18N'));
    
    // the parameters
    $params = $request->getParameter($this->form->getName());
    
    $past = sfConfig::get('app_control_past') ? sfConfig::get('app_control_past') : '6 hours';
    $future = sfConfig::get('app_control_future') ? sfConfig::get('app_control_future') : '1 day';
    
    $q = Doctrine::getTable('Checkpoint')->createQuery('c')->select('c.*');
    $q->leftJoin('c.Event e')
      ->leftJoin('e.Manifestations m')
      ->andWhereIn('e.meta_event_id',array_keys($this->getUser()->getMetaEventsCredentials()))
      ->andWhere('m.happens_at < ?',date('Y-m-d H:i',strtotime('now + '.$future)))
      ->andWhere('manifestation_ends_at(m.happens_at, m.duration) >= ?',date('Y-m-d H:i',strtotime('now - '.$past)))
    ;
    $this->form->getWidget('checkpoint_id')->setOption('query',$q);
    
    // retrieving the configurate field <- need some improvement for a composite nature : qrcode / id if failing (for instance)
    $field = sfConfig::get('app_tickets_id','id');
    if ( !is_array($field) )
      $field = array($field);
    
    if ( count($request->getParameter($this->form->getName())) > 0 )
    {
      $params = $request->getParameter($this->form->getName());
      
      // creating tickets ids array
      if ( ($tmp = json_decode($params['ticket_id'], true, 512, JSON_BIGINT_AS_STRING)) && is_array($tmp) )
      {
        $params['ticket_id'] = $tmp; // json array
        
        // case of a scan of a MemberCard
        if ( isset($tmp['type']) && $tmp['type'] == 'MemberCard' )
        try
        {
          $card = Doctrine::getTable('MemberCard')->find($tmp['member_card_id']);
          
          if ( !$card )
          {
            $params['ticket_id'] = null;
            $this->errors[] = __('The membercard "%%mc%%" does not exist.', array('%%mc%%' => $tmp['member_card_id']));
            $this->success = false;
            return 'Result';
          }
          
          $manifestation = Doctrine::getTable('Manifestation')->createQuery('m')
            ->leftJoin('e.Checkpoints c')
            ->andWhere('c.id = ?', $params['checkpoint_id'])
            ->orderBy('@extract(epoch from m.happens_at - now())')
            ->select('m.*')
            ->fetchOne();
          
          $mcps = new Doctrine_Collection('MemberCardPrice');
          // first, the oriented prices
          foreach ( $card->MemberCardPrices as $mcp )
          if ( $mcp->event_id == $manifestation->event_id )
            $mcps[] = $mcp;
          // then, the agnostic prices
          if ( $mcps->count() == 0 )
          foreach ( $card->MemberCardPrices as $mcp )
          if ( !$mcp->event_id )
            $mcps[] = $mcp;
          
          // if some prices have been found, we can continue...
          if ( $mcps->count() > 0 )
          {
            $price = null;
            foreach ( $manifestation->PriceManifestations as $pm )
            if ( in_array($pm->price_id, $mcps->toKeyValueArray('id', 'price_id')) )
            if ( is_null($price)
              || !is_null($price) && $pm->value > $price->value && $pm->price_id == $price->price_id )
              $price = $pm;
            foreach ( $manifestation->Gauges as $gauge )
            foreach ( $gauge->PriceGauges as $pg )
            if ( in_array($pg->price_id, $mcps->toKeyValueArray('id', 'price_id')) )
            if ( is_null($price)
              || !is_null($price) && $pg->value > $price->value && $pg->price_id == $price->price_id )
              $price = $pg;

            $existing_ticket =  Doctrine::getTable('Ticket')->createQuery('t')
              ->andWhere('t.printed_at IS NOT NULL OR t.integrated_at IS NOT NULL OR t.cancelling IS NOT NULL')
              ->andWhere('t.duplicating IS NULL')
              ->andWhere('t.member_card_id = ?', $card->id)
              ->andWhere('t.manifestation_id = ?', $manifestation->id)
              ->orderBy('t.created_at DESC')
              ->fetchOne();
            
            if ( $existing_ticket )
            {
              $params['ticket_id'] = $existing_ticket->id;
            }
            else 
            if ( $price )
            {
              $ticket = new Ticket;
              $ticket->Transaction = new Transaction;
              $ticket->integrated_at = date('Y-m-d H:i:s');
              $ticket->automatic = true;
              $ticket->Manifestation = $manifestation;
              $ticket->MemberCard = $card;
              
              $ticket->price_id = $price->price_id;
              $ticket->value = $price->value;
              
              if ( $price instanceof PriceGauge )
                $ticket->gauge_id = $price->gauge_id;
              else // PriceManifestation
              {
                foreach ( $manifestation->Gauges as $gauge )
                if ( $gauge->free > 0 )
                if ( in_array($gauge->workspace_id, $price->Price->Workspaces->getPrimaryKeys()) )
                {
                  $ticket->gauge_id = $gauge->id;
                  break;
                }
              }
              
              // first pass for prerequisites
              $ticket->save();
              
              // the payment
              if ( $ticket->value + $ticket->taxes > 0 )
              {
                $payment = new Payment;
                $payment->Method = Doctrine::getTable('PaymentMethod')->createQuery('pm')
                  ->andWhere('pm.member_card_linked = ?', true)
                  ->orderBy('NOT pm.display, pm.id')
                  ->fetchOne()
                ;
                $payment->value = $ticket->value + $ticket->taxes; //$ticket->Transaction->getPrice(false, true) - $ticket->Transaction->getPaid();
                //$payment->MemberCard = $card;
                $ticket->Transaction->Payments[] = $payment;
              }
              $ticket->Transaction->contact_id = $card->contact_id;
              
              // second pass to finish stuff
              $ticket->Transaction->save();
              
              $params['ticket_id'] = $ticket->id;
            }
          }
          else
          {
            $params['ticket_id'] = null;
            $this->errors[] = __('The membercard "%%mc%%" is not valid for the event "%%event%%".', array('%%mc%%' => $card->MemberCardType, '%%event%%' => $manifestation->Event));
            $this->success = false;
            return 'Result';
          }
        } catch ( liEvenementException $e ) {
          // error controling a MemberCard
          error_log('Error controlling a MemberCard on a Checkpoint: '.$e->getMessage());
          $ticket->delete();
          $params['ticket_id'] = null;
          $this->errors[] = __($e->getMessage());
        }
        
      }
      elseif ( in_array('othercode', $field) )
        $params['ticket_id'] = array(preg_replace('/!$/', '', $params['ticket_id']));
      else
      {
        $tmp = explode(',',$params['ticket_id']);
        if ( count($tmp) == 1 )
          $tmp = preg_split('/\s+/',$params['ticket_id']);
        $params['ticket_id'] = array();
        foreach ( $tmp as $key => $ids )
        {
          $ids = explode('-',$ids);
          
          if ( count($ids) > 0 && isset($ids[1]) )
          for ( $i = intval($ids[0]) ; $i <= intval($ids[1]) ; $i++ )
            $params['ticket_id'][$i] = $i;
          else
            $params['ticket_id'][] = $ids[0];
        }
      }
      if ( !is_array($params['ticket_id']) )
        $params['ticket_id'] = array($params['ticket_id']);
      // decode EAN if it exists
      if ( in_array('id', $field) )
      foreach ( $params['ticket_id'] as $key => $value )
      {
        $value = preg_replace('/!$/', '', $value);
        if ( (strlen($value) == 13 || strlen($value) == 12 ) && substr($value,0,1) === '0' )
        {
          try { $value = liBarcode::decode_ean($value); }
          catch ( sfException $e )
          { $value = intval($value); }
          $params['ticket_id'][$key] = $value;
        }
        else
          $params['ticket_id'][$key] = intval($value);
      }
      
      if ( !in_array('id', $field) && !in_array('othercode', $field) && intval($params['ticket_id'][0]).'' === ''.$params['ticket_id'][0] )
        $field = array('id');
      
      // filtering the checkpoints
      if ( isset($params['ticket_id'][0]) && $params['ticket_id'][0] )
      {
        $tmp = $field;
        $q->leftJoin('m.Tickets t')
          ->where('(TRUE')
          ->andWhereIn('t.'.($f = array_shift($tmp)).' IS NOT NULL AND t.'.$f, $params['ticket_id']);
        foreach ( $tmp as $f )
          $q->orWhereIn("t.$f", $params['ticket_id']);
        $q->andWhere('TRUE)');
      }
      
      if ( $params['checkpoint_id'] && count($params['ticket_id']) > 0 )
      {
        $q = Doctrine::getTable('Checkpoint')->createQuery('c')
          ->select('c.*')
          ->leftJoin('c.Event e')
          ->leftJoin('e.Manifestations m')
          ->leftJoin('m.Tickets t')
          ->andWhere('c.id = ?', $params['checkpoint_id']);
        $tmp = $field;
        $q->andWhere('(TRUE')
          ->andWhereIn('t.'.($f = array_shift($tmp)).' IS NOT NULL AND t.'.$f, $params['ticket_id']);
        foreach ( $tmp as $f )
          $q->orWhereIn("t.$f IS NOT NULL AND t.$f", $params['ticket_id']);
        $q->andWhere('TRUE)');
        $checkpoint = $q->fetchOne();
        
        $cancontrol = $checkpoint instanceof Checkpoint;
        if ( !$cancontrol )
          $this->errors[] = __('The ticket #%%id%% is unfoundable in the list of available tickets', array('%%id%%' => implode(', #', $params['ticket_id'])));
        else
        {
          $q = Doctrine::getTable('Ticket')->createQuery('tck')
            ->leftJoin('tck.Controls c WITH c.checkpoint_id = ?', $params['checkpoint_id'])
            ->leftJoin('c.User u')
            ->leftJoin('tck.Price p')
            ->orderBy('c.id DESC, tck.id DESC')
          ;
          $tmp = $field;
          $q->andWhere('(TRUE')
            ->andWhereIn('tck.'.($f = array_shift($tmp)).' IS NOT NULL AND tck.'.$f, $params['ticket_id']);
          foreach ( $tmp as $f )
            $q->orWhereIn("tck.$f IS NOT NULL AND tck.$f", $params['ticket_id']);
          $q->andWhere('TRUE)');
          
          foreach ( $q->execute() as $ticket )
          {
            if ( $ticket->Manifestation->happens_at > date('Y-m-d H:i',strtotime('now + '.$future)) )
            {
              // It's too soon pal !
              $this->error_tickets[$ticket->id] = $ticket;
              $this->errors[] = __('Too soon for ticket #%%id%% (gates will open at %%datetime%%)', array(
                '%%id%%' => $ticket->id,
                '%%datetime%%' => date('Y-m-d H:i',strtotime($ticket->Manifestation->happens_at . ' - ' .$future))
              ));
            }
            elseif ( strtotime($ticket->Manifestation->ends_at) < strtotime('now - '.$past) )
            {
               // It's too late man !
               $this->error_tickets[$ticket->id] = $ticket;
               $this->errors[] = __('Too late for ticket #%%id%% (gates closed at %%datetime%%)', array(
                 '%%id%%' => $ticket->id,
                 '%%datetime%%' => date('Y-m-d H:i',strtotime($ticket->Manifestation->end_at . ' - ' .$past))
               ));
            }
            else
            {
              switch ($checkpoint->type)
              {
                case 'entrance':
                  $cancontrol = false;
                  $this->controls       = new Doctrine_Collection('Control');
                  
                  // the ticket is in its duration of validity
                  if ( $ticket->Price->x_days_valid > 0
                    && $ticket->Controls->count() > 0
                    && $ticket->Controls[0]->created_at >= date('Y-m-d', strtotime(($control->Ticket->Price->x_days_valid-1).' days ago')) )
                    ; // nothing to do, just ignore this control
                  elseif ( $ticket->Controls->count() > 0 )
                  foreach ( $ticket->Controls as $control )
                  {
                    $this->error_tickets[$ticket->id] = $ticket;
                    $this->errors[] = __('The ticket #%%id%% has been already controlled on this checkpoint before (%%datetime%% by %%user%%)', array(
                      '%%id%%' => $ticket->id,
                      '%%datetime%%' => $control->created_at,
                      '%%user%%' => (string)$control->User,
                    ));
                    
                    // adding a failure in the control log if a ticket is being controled twice
                    $failure = new FailedControl;
                    $params['ticket_id'] = $ticket->id;
                    $failure->complete($params);
                  }
                  else
                  {
                    $cancontrol = true;
                    $this->tickets[$ticket->id] = $ticket;
                  }
                  break;
                case 'info':
                  $cancontrol = true;
                  $this->tickets[$ticket->id] = $ticket;
                  break;
              }
            }
          }
        }
        
        $comments = array();
        foreach ( $this->tickets as $ticket )
        if ( $ticket instanceof Ticket )
        foreach ( array($ticket->DirectContact, $ticket->Transaction->Contact) as $contact )
        if ( trim($contact->flash_on_control) )
          $comments[] = trim($contact->flash_on_control);
        $params['comment'] = $comments ? implode("\n", $comments) : NULL;
        
        if ( $cancontrol )
        {
          if ( $checkpoint->id )
          {
            $err = $tck = array();
            $ids = $this->tickets->getPrimaryKeys();
            foreach ( $ids as $id )
            {
              $this->form = new ControlForm;
              $this->form->forceField('id');
              $params['ticket_id'] = $id;
              $this->form->bind($params, $request->getFiles($this->form->getName()));
              if ( $this->form->isValid() ) try
              {
                $this->form->save();
                // do only one loop if the config says to control tickets one by one
                if ( sfConfig::get('app_control_type', 'group') == 'onebyone' )
                {
                  $cpt = 0;
                  foreach ( $ids as $id2 )
                  if ( $id2 != $id )
                  {
                    $form = new ControlForm;
                    $form->forceField('id');
                    $params['ticket_id'] = $id;
                    $form->bind($params, $request->getFiles($form->getName()));
                    if ( $form->isValid() )
                      $cpt++;
                    unset($this->tickets[$id2]);
                  }
                  if ( $cpt > 0 )
                    $this->errors[] = __('You still have %%nb%% control(s) left on this meta-ticket', array('%%nb%%' => $cpt));
                  break;
                }
              } catch ( liEvenementException $e ) { error_log('TicketActions::executeControl() - '.$e->getMessage().' Passing by.'); }
              else
              {
                $err[] = $id;
                if ( isset($this->tickets[$id]) )
                {
                  $tck[$id] = $this->tickets[$id];
                  if ( !$this->form->getObject()->Checkpoint->mightControl($id) )
                  {
                    // if in a single checkpoint verification, we should unset uncontrolable tickets
                    if ( !$multipleCheckpoints ) {
                      unset($this->tickets[$id]);
                    }
                    continue;
                  }
                }
                else
                  $tck[$id] = Doctrine::getTable('Ticket')->find($id);
                
                $failure = new FailedControl;
                if ( isset($this->tickets[$id]) )
                  $params['ticket_id'] = $id;
                $failure->complete($params);
              }
            }
            foreach ( $err as $e )
              $this->errors[] = __('An error occurred controlling your ticket #%%id%%.', array('%%id%%' => $e))
                .($tck[$e] instanceof Ticket && !$tck[$e]->printed_at && !$tck[$e]->integrated_at ? ' '.__('This ticket is not sold yet.') : '');
            $this->success = count($err) < count($ids);
            return 'Result';
          }
          else // !$checkpoint->id
          {
            if ( !$params['checkpoint_id'] )
            {
              $this->getUser()->setFlash('error',__("Don't forget to specify a checkpoint"));
              $params['ticket_id'] = implode(',',$params['ticket_id']);
              $this->form->bind($params);
            }
            else
            {
              $this->success = false;
              $failure = new FailedControl;
              $failure->complete($params);
              return 'Result';
            }
          }
        }
        else // !$cancontrol
        {
          $this->success = false;
          foreach ( $this->error_tickets as $ticket )
          {
            $failure = new FailedControl;
            if ( $ticket instanceof Ticket )
              $params['ticket_id'] = $ticket->id;
            $failure->complete($params);
          }
          if ( $this->tickets->count() == 0 ) {
            $this->tickets = $this->error_tickets;
          }
          return 'Result';
        }
      }
      else
      {
        $this->success = false;
        $this->errors[] = __("Don't forget to specify a checkpoint and a ticket id");
        return 'Result';
      }
    }
    
    return 'Success';
