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
    
    $this->duplicate = $request->getParameter('duplicate') == 'true';
    $this->tickets = array();
    
    foreach ( $this->transaction->Tickets as $ticket )
    {
      $newticket = $ticket->copy();
      $newticket->save();
      $ticket->duplicate = $newticket->id;
      $ticket->save();
      
      if ( $request->getParameter('duplicate') == 'true' )
      {
        // grouped tickets
        if ( strcasecmp($ticket->price_name,$request->getParameter('price_name')) == 0
          && $ticket->printed
          && $ticket->manifestation_id == $request->getParameter('manifestation_id') )
        {
          // grouped tickets
          if ( sfConfig::has('app_tickets_authorize_grouped_tickets')
            && sfConfig::get('app_tickets_authorize_grouped_tickets')
            && $request->hasParameter('grouped_tickets') )
          {
            if ( isset($this->tickets[$id = $ticket->gauge_id.'-'.$ticket->price_id.'-'.$ticket->transaction_id]) )
            {
              $this->tickets[$id]['ticket']->NextGroupedWith = $ticket;
              $this->tickets[$id]['ticket']->save();
              $this->tickets[$id]['ticket'] = $ticket;
              $this->tickets[$id]['nb']++;
            }
            else
              $this->tickets[$id] = array('nb' => 1, 'ticket' => $newticket);
          }
          
          // normal tickets
          else
            $this->tickets[] = $ticket;
        }
      }
      else
      {
        //$this->duplicate = false;
        if ( !$ticket->printed && !$ticket->integrated )
        {
          $ticket->sf_guard_user_id = NULL;
          if ( $ticket->Manifestation->no_print )
          {
            try {
              $ticket->integrated = true;
              $ticket->save();
            }
            catch ( liEvenementException $e )
            { }
          }
          else
          {
            try {
              $ticket->printed = true;
              $ticket->save();
              
              // grouped tickets
              if ( sfConfig::has('app_tickets_authorize_grouped_tickets')
                && sfConfig::get('app_tickets_authorize_grouped_tickets')
                && $request->hasParameter('grouped_tickets') )
              {
                if ( isset($this->tickets[$id = $ticket->gauge_id.'-'.$ticket->price_id.'-'.$ticket->transaction_id]) )
                {
                  $this->tickets[$id]['ticket']->NextGroupedWith = $ticket;
                  $this->tickets[$id]['ticket']->save();
                  $this->tickets[$id]['ticket'] = $ticket;
                  $this->tickets[$id]['nb']++;
                }
                else
                  $this->tickets[$id] = array('nb' => 1, 'ticket' => $ticket);
              }
            
              // normal tickets
              else
                $this->tickets[] = $ticket;
            }
            catch ( liEvenementException $e )
            { }
          }
        }
      }
      
      if ( count($this->tickets) >= 200 )
      {
        $this->print_again = true;
        break;
      }
    }
    
    // avoid that update, because it updates every tickets' updated_at value and it's quite useless
    //$this->transaction->updated_at = NULL;
    //$this->transaction->save();
    
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
