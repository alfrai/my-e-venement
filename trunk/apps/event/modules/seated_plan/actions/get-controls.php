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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $ids = $request->getParameter('gauges_list', false);
    
    if ( $request->getParameter('gauge_id') )
    {
      $this->executeEdit($request);
      $this->seated_plans = new Doctrine_Collection('SeatedPlan');
      $this->seated_plans[] = $this->seated_plan;
    }
    elseif ( is_array($ids) )
    {
      $q = Doctrine::getTable('SeatedPlan')->createQuery('sp')
        ->leftJoin('sp.Workspaces ws')
        ->leftJoin('ws.Gauges g')
        ->andWhereIn('g.id', $ids)
        ->leftJoin('g.Manifestation m')
        ->leftJoin('m.Location l')
        ->andWhere('l.id = sp.location_id')
      ;
      $this->seated_plans = $q->execute();
      $this->forward404Unless($this->seated_plans->count() > 0);
    }
    elseif ( $id = $request->getParameter('id') )
    {
      $q = Doctrine::getTable('SeatedPlan')->createQuery('sp')
        ->andWhere('sp.id = ?', $id)
        ->leftJoin('sp.Seats s')
      ;
      $this->seated_plans = $q->execute();
      $this->forward404Unless($this->seated_plans->count() > 0);
    }
    
    $this->occupied = array();
    $this->transaction_id = intval($request->getParameter('transaction_id', 0));
    sfConfig::set('sf_escaping_strategy', false);
    
    if ( $this->getUser()->hasCredential('tck-seat-allocation')
      && (($gid = intval($request->getParameter('gauge_id', 0))) > 0 || is_array($ids)) )
    {
      $q = Doctrine::getTable('Ticket')->createQuery('tck')
        ->select('tck.*, t.*, c.*, pro.*, org.*, o.*, pc.*')
        ->leftJoin('tck.DirectContact dc')
        ->leftJoin('tck.Seat s')
        ->leftJoin('tck.Controls ctrl')
        ->leftJoin('ctrl.Checkpoint cp WITH cp.legal = ?', true)
        ->leftJoin('tck.Transaction t')
        ->leftJoin('t.Contact c WITH dc.id IS NULL')
        ->leftJoin('t.Professional pro WITH dc.id IS NULL')
        ->leftJoin('pro.Organism org')
        ->leftJoin('pro.Contact pc')
        ->leftJoin('t.Order o')
        ->leftJoin('s.SeatedPlan sp')
        ->leftJoin('tck.Cancelling cancel')
        ->andWhere('tck.cancelling IS NULL')
        ->andWhere('duplicatas.id IS NULL AND cancel.id IS NULL')
        ->andWhere('tck.seat_id IS NOT NULL')
        ->leftJoin('tck.Manifestation m')
        ->leftJoin('m.Gauge g')
        ->orderBy('ctrl.updated_at DESC')
        ->andWhere('tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL')
      ;
      if ( $request->getParameter('gauge_id') && $request->getParameter('id') )
        $q->andWhere('g.id = ?', $request->getParameter('gauge_id'))
          ->andWhere('sp.id = ?', $request->getParameter('id'));
      if ( is_array($ids) )
        $q->andWhereIn('g.id', $ids);
      
      foreach ( $q->execute() as $ticket )
      {
        if ( $ticket->contact_id )
          $contact = (string)$ticket->DirectContact;
        else
          $contact = is_object($ticket->Transaction->Professional) && $ticket->Transaction->professional_id
            ? $ticket->Transaction->Contact.' '.$ticket->Transaction->Professional
            : (string)$ticket->Transaction->Contact
          ;
        $this->occupied[$ticket->Seat->name] = array(
          'type'            => $ticket->Controls->count() > 0 ? 'free' : 'printed',
          'transaction_id'  => '#'.$ticket->transaction_id,
          'controlled_at'   => $ticket->Controls[0]->created_at,
          'ticket_id'       => $ticket->id,
          'price_id'        => $ticket->price_id,
          'gauge_id'        => $ticket->gauge_id,
          'spectator'       => $contact,
        );
      }
    }
    
    if (!( $request->hasParameter('debug') && sfConfig::get('sf_web_debug', false) ))
    {
      sfConfig::set('sf_web_debug', false);
      return 'Json';
    }
    return 'Success';
