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
  $gauges_ok = new Doctrine_Collection('Gauge');
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
      $gauges_ok[] = $gauge;
  }
  
  if ( $gauges_ok->count() == 0 )
  {
    error_log('The maximum number of tickets is reached for online sales on manifestation #'.$gauge->manifestation_id.' and gauges '.$params['group_name']);
    return 'Error';
  }
  
  // to give seats to tickets that need it
  foreach ( $gauges_ok as $gauge )
  {
    $seater = new Seater($gauge->id);
    $free_seats = $seater->findSeatsExcludingOrphans($params['qty']);
    if ( $free_seats )
      break;
  }
  
  // tickets creation
  $tickets = new Doctrine_Collection('Ticket');
  for ( $i = 0 ; $i < $params['qty'] ; $i++ )
  {
    $ticket = new Ticket;
    $this->getUser()->getTransaction()->Tickets[] = $ticket;
    $this->Transaction = $this->getUser()->getTransaction();
    $ticket->price_id = $params['price_id'];
    $ticket->Gauge = $gauge;
    $ticket->Seat = $free_seats->getFirst(); // use the first seat still available
    unset($free_seats[$free_seats->key()]);  // remove the used seat from the free seats pool
    $tickets[] = $ticket;
  }

  
  // remove tickets that have no seat_id given
  foreach ( $tickets as $key => $ticket )
  if ( !$ticket->seat_id )
    unset($tickets[$key]);
  
  // linked products
  foreach ( $tickets as $ticket )
    $ticket->addLinkedProducts();
  $tickets->save();
  
  $this->dispatcher->notify($event = new sfEvent($this, 'pub.after_adding_tickets', array()));
  
  // return back the list of real tickets
  $this->data = array('tickets' => array());
  foreach ( $this->getUser()->getTransaction()->Tickets as $ticket )
  if ( $ticket->id )
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
