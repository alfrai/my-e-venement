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
  $this->json = array('error' => false, 'success' => array(
    'new'     => array(),
    'deleted' => array(),
    'moved'   => array(),
  ));
  $this->debug($request);
  
  $cpt = 0;
  foreach ( $request->getParameter('seats', array()) as $seat )
  {
    foreach ( array('seat_id', 'gauge_id', 'price_id', 'ticket_id') as $field )
    if ( !isset($seat[$field]) )
      $seat[$field] = false;
  
    if ( !$seat['gauge_id'] )
      throw new liOnlineSaleException('You asked for a ticket modification without providing any gauge_id...');
    
    // prerequisite (security)
    $q = Doctrine::getTable('Gauge')->createQuery('g',false)
      ->andWhere('g.id = ?', $seat['gauge_id'])
      ->leftJoin('g.Manifestation m')
      ->leftJoin('m.Event e')
      ->andWhereIn('g.workspace_id', array_keys($this->getUser()->getWorkspacesCredentials()))
      ->andWhereIn('e.meta_event_id', array_keys($this->getUser()->getMetaEventsCredentials()))
    ;
    if ( isset($seat['price_id']) && $seat['price_id'] )
      $q->leftJoin('m.Prices p')->andWhere('p.id = ?', $seat['price_id']);
    if ( isset($seat['seat_id']) && $seat['seat_id'] )
      $q->leftJoin('g.Workspace ws')
        ->leftJoin('ws.SeatedPlans sp WITH sp.location_id = m.location_id')
        ->leftJoin('sp.Seats s')
        ->andWhere('s.id = ?', $seat['seat_id'])
      ;
    if ( $q->count() == 0 )
      throw new liOnlineSaleException('Someone is trying to add tickets for the forbidden gauge #'.$seat['gauge_id']);
    
    // all must have gauge_id
    // with price_id: to add
    //  - with seat_id: seat_id to steal to a WIP
    //  - without seat_id: create a Ticket w/o a Seat
    // without price_id (with seat_id): to remove (WIP or normal)
    //  - with seat_id: based on Seat
    //  - without seat_id: needs a ticket_id to remove the ticket
    
    // Ticket creation with a blank seat || deletion
    if ( !$seat['seat_id'] )
    {
      if ( $seat['ticket_id'] )
      {
        if (!( $ticket = Doctrine::getTable('Ticket')->find($seat['ticket_id']) ))
          throw new liOnlineSaleException('The targetted ticket #'.$seat['ticket_id'].' does not exist.');
        $this->json['success']['deleted'][] = array(
          'ticket_id' => $ticket->id,
          'seat_id'   => NULL,
          'gauge_id'  => $ticket->gauge_id,
          'price_id'  => $ticket->price_id,
          'price_name'=> $ticket->price_name,
        );
        $ticket->delete();
        unset($ticket);
      }
      else
      {
        $ticket = new Ticket;
        $ticket->gauge_id = $seat['gauge_id'];
        $ticket->transaction_id = $this->getUser()->getTransactionId();
        $ticket->price_id = $seat['price_id'];
        $ticket->save();
        
        $this->json['success']['new'][] = array(
          'ticket_id' => $ticket->id,
          'seat_id'   => NULL,
          'gauge_id'  => $ticket->gauge_id,
          'price_id'  => $ticket->price_id,
          'price_name'=> $ticket->price_name,
        );
      }
    }
    
    // all but Ticket creation or deletion
    else
    {
      $q = Doctrine::getTable('Ticket')->createQuery('tck')
        ->select('tck.*')
        ->leftJoin('tck.Seat s')
        ->leftJoin('tck.Gauge g')
        ->andWhere('tck.transaction_id = ?', $this->getUser()->getTransactionId())
        ->andWhere('g.id = ?', $seat['gauge_id'])
        ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL AND tck.cancelling IS NULL AND tck.duplicating IS NULL')
      ;
      if ( $seat['seat_id'] )
        $q->andWhere('s.id = ?', $seat['seat_id']);
      elseif ( $seat['ticket_id'] )
        $q->andWhere('tck.id = ?', $seat['ticket_id']);
      else
        throw new liOnlineSaleException('We need more parameter to process tickets manipulations');
      
      $ticket = $q->fetchOne();
      
      if ( !$ticket )
        return $this->jsonError('The given seat is not available for this gauge, try again.');
      
      // upgrade a WIP to a real ticket // affecting a price
      if ( isset($seat['price_id']) && $seat['price_id'] )
      {
        $ticket->price_name = NULL;
        $ticket->price_id = $seat['price_id'];
      }
      // downgrade a real ticket to a WIP
      else
      {
        $ticket->price_name = sfConfig::get('app_tickets_wip_price', 'WIP');
        $ticket->price_id = NULL;
      }
      
      $this->json['success']['moved'][] = array(
        'ticket_id' => $ticket->id,
        'seat_id'   => $ticket->seat_id,
        'gauge_id'  => $ticket->gauge_id,
        'price_id'  => $ticket->price_id,
        'price_name'=> $ticket->price_name,
      );
      if ( !$ticket->trySave() )
        return $this->jsonError('An error occured saving the price for ticket #'.$ticket->id.'. '.$cpt.' ticket(s) were successfull.');
    }
  }
  
  return 'Success';
