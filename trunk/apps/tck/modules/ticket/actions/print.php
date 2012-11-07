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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    if ( !($this->getRoute() instanceof sfObjectRoute) )
      return $this->redirect('ticket/sell');
    
    //$this->transaction = $this->getRoute()->getObject();
    $q = Doctrine::getTable('Transaction')
      ->createQuery('t')
      ->andWhere('t.id = ?',$request->getParameter('id'))
      ->andWhere('tck.duplicate IS NULL')
      ->leftJoin('m.Location l')
      ->leftJoin('m.Organizers o')
      ->leftJoin('m.Event e')
      ->leftJoin('e.MetaEvent me')
      ->leftJoin('e.Companies c')
      ->leftJoin('tck.Gauge g')
      ->leftJoin('g.Workspace ws')
      ->orderBy('m.happens_at, tck.price_name, tck.id');
    
    // partial printing
    if ( $request->hasParameter('toprint') )
    {
      $this->toprint = $tids = $request->getParameter('toprint');
      
      if ( !is_array($tids) ) $tickets = array($tids);
      foreach ( $tids as $key => $value )
        $tids[$key] = intval($value);
      
      $q->andWhereIn('tck.id',$tids);
    }
    
    $this->transaction = $q->fetchOne();
    $this->manifestation_id = $request->getParameter('manifestation_id');
    
    $fingerprint = NULL;
    $this->print_again = false;
    $this->grouped_tickets = false;
    $this->duplicate = $request->getParameter('duplicate') == 'true';
    $this->tickets = array();
    $update = array('printed' => array(), 'integrated' => array());
    
    // grouped tickets
    if ( sfConfig::has('app_tickets_authorize_grouped_tickets')
      && sfConfig::get('app_tickets_authorize_grouped_tickets')
      && $request->hasParameter('grouped_tickets') )
    {
      $fingerprint = date('YmdHis').'-'.$this->getUser()->getId();
      $this->grouped_tickets = true;
      
      $cpt = 0;
      foreach ( $this->transaction->Tickets as $ticket )
      {
        try {
          // duplicates
          if ( $request->getParameter('duplicate') == 'true' )
          {
            if ( strcasecmp($ticket->price_name,$request->getParameter('price_name')) == 0
              && $ticket->printed
              && $ticket->manifestation_id == $request->getParameter('manifestation_id') )
            {
              if ( $cpt >= 150 )
              {
                $this->print_again = false;
                break;
              }
        
              $newticket = $ticket->copy();
              $newticket->sf_guard_user_id = NULL;
              $newticket->created_at = NULL;
              $newticket->updated_at = NULL;
              $newticket->grouping_fingerprint = $fingerprint;
              $newticket->save();
              
              $ticket->duplicate = $newticket->id;
              $ticket->updated_at = NULL;
              $ticket->save();
              
              if ( isset($this->tickets[$id = $ticket->gauge_id.'-'.$ticket->price_id.'-'.$ticket->transaction_id]) )
              {
                $this->tickets[$id]['ticket'] = $newticket;
                $this->tickets[$id]['nb']++;
              }
              else
                $this->tickets[$id] = array('nb' => 1, 'ticket' => $newticket);
              
              $cpt++;
            }
          }
          
          else // not duplicates
          if ( !$ticket->printed && !$ticket->integrated )
          {
            if ( $cpt >= 150 )
            {
              $this->print_again = true;
              break;
            }
        
            if ( $ticket->Manifestation->no_print )
              $update['integrated'][$ticket->id] = $ticket->id;
            else
            {
              $update['printed'][$ticket->id] = $ticket->id;
              if ( isset($this->tickets[$id = $ticket->gauge_id.'-'.$ticket->price_id.'-'.$ticket->transaction_id]) )
              {
                $this->tickets[$id]['ticket'] = $ticket; // adding a new one not saved
                $this->tickets[$id]['nb']++;
              }
              else // first ticket of the chain
                $this->tickets[$id] = array('nb' => 1, 'ticket' => $ticket);
              
              $cpt++;
            }
          }
        }
        catch ( liEvenementException $e )
        { }
      }
      
      if ( $request->getParameter('duplicate') != 'true' )
      foreach ( $this->tickets as $ticket )
        $update['printed'][$ticket['ticket']->id] = $ticket['ticket']->id;
    }
    
    // normal / not grouped tickets
    else
    {
      foreach ( $this->transaction->Tickets as $ticket )
      {
        if ( count($this->tickets) >= 150 )
        {
          $this->print_again = true;
          break;
        }
        
        try {
          // duplicates
          if ( $request->getParameter('duplicate') == 'true' )
          {
            if ( strcasecmp($ticket->price_name,$request->getParameter('price_name')) == 0
              && $ticket->printed
              && $ticket->manifestation_id == $request->getParameter('manifestation_id') )
            {
              $newticket = $ticket->copy();
              $newticket->sf_guard_user_id = NULL;
              $newticket->created_at = NULL;
              $newticket->updated_at = NULL;
              $newticket->save();
              
              $ticket->duplicate = $newticket->id;
              $ticket->updated_at = NULL;
              $ticket->save();
              $this->tickets[] = $ticket;
            }
          }
          
          else // $this->duplicate == false
          {
            if ( !$ticket->printed && !$ticket->integrated )
            {
              if ( $ticket->Manifestation->no_print )
                $update['integrated'][$ticket->id] = $ticket->id;
              else
              {
                $update['printed'][$ticket->id] = $ticket->id;
                $this->tickets[] = $ticket;
              }
            }
          }
        }
        catch ( liEvenementException $e )
        { }
      }
    }
    
    // bulk updates
    foreach ( $update as $type => $ids )
    if ( count($ids) > 0 )
    {
      $q = Doctrine_Query::create()->update()
        ->from('Ticket t')
        ->whereIn('t.id',$ids)
        ->set('t.'.$type,'true')
        ->set('t.updated_at','NOW()')
        ->set('t.sf_guard_user_id',$this->getUser()->getId());
      
      // bulk update for grouped tickets
      if ( sfConfig::has('app_tickets_authorize_grouped_tickets')
        && sfConfig::get('app_tickets_authorize_grouped_tickets')
        && $request->hasParameter('grouped_tickets') )
      {
        if ( is_null($fingerprint) )
          throw new liEvenementException('Printing grouped tickets without a fingerprint is forbidden');
        
        $q->set('t.grouping_fingerprint',"'".$fingerprint."'");
      }
      
      $q->execute();
    }
    
    if ( count($this->tickets) <= 0 )
      $this->setTemplate('close');
    else
    {
      if ( sfConfig::get('app_tickets_id') != 'othercode' )
        $this->setLayout('empty');
      else
      {
        $this->form = new BaseForm();
        
        foreach ( $this->tickets as $ticket )
        {
          $w = new sfWidgetFormInputText();
          $w->setLabel($ticket->Manifestation.' '.$ticket->price_name);
          $this->form->setWidget('['.$ticket->id.'][othercode]',$w);
        }
        $this->form->getWidgetSchema()->setNameFormat('ticket%s');
        
        $this->setTemplate('rfid');
      }
    }
