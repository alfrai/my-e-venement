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
  $this->debug($request);
  $this->data = array();
  $params = $request->getParameter('price_new');
  $config = sfConfig::get('app_tickets_vel', array());
  
  if (!( isset($params['manifestation_id']) && intval($params['manifestation_id']).'' === ''.$params['manifestation_id'] && intval($params['manifestation_id']) > 0 ))
    return 'Error';
  if (!( isset($params['price_id']) && intval($params['price_id']).'' === ''.$params['price_id'] && intval($params['price_id']) > 0 ))
    return 'Error';
  if (!( isset($params['qty']) && intval($params['qty']) > 0 ))
    $params['qty'] = 1;
  
  // retrieve the gauge where can be applyied the future ticket
  $q = Doctrine::getTable('Gauge')->createQuery('g', false)
    ->andWhere('g.manifestation_id = ?', $params['manifestation_id'])
    ->andWhere('g.group_name = ?', $params['group_name'])
    ->andWhere('g.online = ?', true)
    
    ->leftJoin('g.PriceGauges         gpg WITH gpg.price_id IN (SELECT gup.price_id FROM UserPrice gup WHERE gup.sf_guard_user_id = ?)', $this->getUser()->getId())
    ->leftJoin('g.Manifestation m')
    ->leftJoin('m.PriceManifestations mpm WITH mpm.price_id IN (SELECT mup.price_id FROM UserPrice mup WHERE mup.sf_guard_user_id = ?)', $this->getUser()->getId())
    ->andWhere('(gpg.price_id = ? OR mpm.price_id = ?)', array($params['price_id'], $params['price_id']))
    
    ->leftJoin('g.Workspace ws')
    ->leftJoin('ws.SeatedPlans sp WITH sp.location_id = m.location_id')
    ->leftJoin('sp.Seats s')
    ->leftJoin('s.Tickets tck WITH tck.gauge_id = g.id')
    ->andWhere('tck.id IS NULL')
    
    ->orderBy('min(s.rank), gpg.value DESC, ws.name')
    ->select($select = 'g.id, m.id, m.online_limit, gpg.id, gpg.value, ws.id, ws.name')
    ->addSelect('count(DISTINCT s.id) AS nb_seats')
    ->groupBy($select)
  ;
  $gauges = $q->execute();
  if ( $gauges->count() == 0 )
  {
    error_log('No gauge found for this ticket ('.print_r($params,true).')');
    return 'Error';
  }
  
  $success = false;
  foreach ( $gauges as $gauge )
  {
    $this->dispatcher->notify($event = new sfEvent($this, 'pub.before_adding_tickets', array('manifestation' => $gauge->Manifestation)));
    if ( $event->getReturnValue() )
    {
      $success = true;
      break;
    }
  }
  
  if ( !$success )
  {
    error_log('The maximum number of tickets is reached for online sales on manifestation #'.$gauge->manifestation_id.' and gauges '.$params['group_name']);
    return 'Error';
  }
  
  // global limitation
  if ( ($nb = Doctrine::getTable('Ticket')->createQuery('tck')
    ->andWhere('tck.manifestation_id = ?', $gauge->manifestation_id)
    ->andWhere('tck.transaction_id = ?', $this->getUser()->getTransactionId())
    ->count()) + $params['qty'] > $event['max'] )
  {
    $params['qty'] = $event['max'] - $nb;
    $this->message = 'Some tickets have not been added because you reached the limit of tickets for this manifestation.';
  }
  
  // tickets creation
  $tickets = new Doctrine_Collection('Ticket');
  for ( $i = 0 ; $i < $params['qty'] ; $i++ )
  {
    $ticket = new Ticket;
    $ticket->transaction_id = $this->getUser()->getTransactionId();
    $ticket->price_id = $params['price_id'];
    $ticket->gauge_id = $gauge->id;
    $tickets[] = $ticket;
  }

  // to give seats to tickets that need it
  $seater = new Seater($gauge->id);
  $i = 0;
  foreach ( $seater->findSeats($params['qty']) as $seat )
    $tickets[$i++]->Seat = $seat;
  $tickets->save();
  
  // linked products
  foreach ( $tickets as $ticket )
    $ticket->addLinkedProducts();
  $tickets->save();
  
  $this->dispatcher->notify($event = new sfEvent($this, 'pub.after_adding_tickets', array()));
  
  // return back the list of real tickets
  $this->data = array('tickets' => array());
  foreach ( $this->getUser()->getTransaction()->Tickets as $ticket )
  {
    // the json data
    $this->data['tickets'][] = array(
      'ticket_id'         => $ticket->id,
      'seat_name'         => is_object($ticket->Seat) ? (string)$ticket->Seat : (string)Doctrine::getTable('Seat')->find($ticket->seat_id),
      'seat_id'           => $ticket->seat_id,
      'price_name'        => $ticket->price_id ? (string)$ticket->Price : $ticket->price_name,
      'price_id'          => $ticket->price_id,
      'gauge_name'        => (string)$ticket->Gauge,
      'gauge_id'          => $ticket->gauge_id,
      'extra-taxes'       => (float)$ticket->taxes,
      'value'             => (float)$ticket->value,
    );
  }
  
  return 'Success';
