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
    $vel = sfConfig::get('app_tickets_vel');
    if ( !isset($vel['full_seating_by_customer']) ) $vel['full_seating_by_customer'] = false;
     
    $this->json = array(
      'error' => false,
      'success' => false,
    );
     
    if (!( $vel['full_seating_by_customer'] && sfConfig::get('app_tickets_wip_price', 'WIP') ))
      return $this->jsonError('This plateform does not allow this action', $request);
    
    // check if the user is still below the tickets limit
    $q = Doctrine::getTable('Ticket')->createQuery('tck')
      ->andWhere('tck.transaction_id = ?', $this->getUser()->getTransactionId())
      ->leftJoin('tck.Manifestation m')
      ->leftJoin('m.Gauges g')
      ->andWhere('g.id = ?', $request->getParameter('id'))
      ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL AND tck.cancelling IS NULL AND tck.duplicating IS NULL')
    ;
    $vel = sfConfig::get('app_tickets_vel', array());
    if ( $q->count() > (isset($vel['max_per_manifestation']) ? $vel['max_per_manifestation'] : 9) )
      return $this->jsonError('You have already reach the tickets limit for this manifestation, contact us.', $request);
    
    // update or create the ticket
    $q = Doctrine::getTable('Ticket')->createQuery('tck')
      ->andWhere('tck.seat_id IS NULL')
      ->andWhere('tck.transaction_id = ?', $this->getUser()->getTransactionId())
      ->andWhere('tck.gauge_id = ?', $request->getParameter('id'))
      ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL AND tck.cancelling IS NULL AND tck.duplicating IS NULL')
      ->orderBy('tck.value, tck.created_at DESC')
    ;
    if ( $q->count() > 0 )
      $ticket = $q->fetchOne();
    else
    {
      $ticket = new Ticket;
      $ticket->transaction_id = $this->getUser()->getTransactionId();
      $ticket->price_name = sfConfig::get('app_tickets_wip_price', 'WIP');
      $ticket->gauge_id = $request->getParameter('id');
      $ticket->value = 0;
      $ticket->vat = 0;
    }
    $ticket->seat_id = $request->getParameter('seat_id');
    
    if ( !$ticket->trySave() )
      return $this->jsonError('Maybe this seat is no longer available, check again.');
    
    $this->json['success'] = array('new' => array(array(
      'ticket_id' => $ticket->id,
      'seat_id'   => $ticket->seat_id,
      'gauge_id'  => $ticket->gauge_id,
      'price_id'  => $ticket->price_id,
      'price_name'=> $ticket->price_name,
      'seat_name' => (string)$ticket->Seat,
    )));
    
    $this->json['success']['orphans'] = $this->getContext()->getConfiguration()->getOrphans($this->getUser()->getTransaction(), array('gauge_id' => $ticket->gauge_id));
    
    $this->debug($request);
    return 'Success';
