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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

    $this->executeEdit($request);
    $this->occupied = array();
    $this->transaction_id = intval($request->getParameter('transaction_id', 0));
    sfConfig::set('sf_escaping_strategy', false);
    
    if ( $this->getUser()->hasCredential('tck-seat-allocation')
      && intval($request->getParameter('gauge_id', 0)) > 0 )
    {
      $q = Doctrine::getTable('Ticket')->createQuery('tck')
        ->select('tck.*, t.*, c.*, pro.*, org.*, o.*, pc.*')
        ->leftJoin('tck.Seat s')
        ->leftJoin('tck.Transaction t')
        ->leftJoin('t.Contact c')
        ->leftJoin('t.Professional pro')
        ->leftJoin('pro.Organism org')
        ->leftJoin('pro.Contact pc')
        ->leftJoin('t.Order o')
        ->leftJoin('s.SeatedPlan sp')
        ->leftJoin('tck.Cancelling cancel')
        ->andWhere('tck.cancelling IS NULL')
        ->andWhere('duplicatas.id IS NULL AND cancel.id IS NULL')
        ->andWhere('tck.seat_id IS NOT NULL')
        ->andWhere('sp.id = ?', $request->getParameter('id'))
        ->leftJoin('tck.Manifestation m')
        ->leftJoin('m.Gauge g')
        ->andWhere('g.id = ?', $request->getParameter('gauge_id'))
      ;
      
      foreach ( $q->execute() as $ticket )
        $this->occupied[$ticket->Seat->name] = array(
          'type' => ($ticket->printed_at || $ticket->integrated_at ? 'printed' : ($ticket->Transaction->Order->count() > 0 ? 'ordered' : 'asked')).($ticket->transaction_id === $this->transaction_id ? ' in-progress' : ''),
          'transaction_id' => '#'.$ticket->transaction_id,
          'spectator'      => $ticket->Transaction->professional_id ? $ticket->Transaction->Professional->Contact.' '.$ticket->Transaction->Professional : (string)$ticket->Transaction->Contact,
        );
    }
