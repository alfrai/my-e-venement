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
  $this->json = array('error' => false, 'success' => false);
  $this->debug($request);
  
  // the query
  $q = Doctrine::getTable('Gauge')->createQuery('g')
    ->leftJoin('ws.SeatedPlans sp')
    
    ->leftJoin('g.Manifestation m')
    ->leftJoin('m.Location l')
    ->andWhere('l.id = sp.location_id')
    
    ->leftJoin('g.Tickets tck')
    ->leftJoin('tck.Seat s')
    ->andWhere('tck.transaction_id = ?', $this->getUser()->getTransaction()->id)
    ->andWhere('tck.seat_id IS NOT NULL')
  ;
  if ( $request->getParameter('gauge_id', false) )
    $q->andWhere('g.id = ?', $request->getParameter('gauge_id'));
  if ( $request->getParameter('manifestation_id', false) )
    $q->andWhere('m.id = ?', $request->getParameter('manifestation_id'));
  if ( $request->getParameter('seat_id', false) )
    $q->andWhere('tck_seat_id = ?', $request->getParameter('seat_id'));
  
  // no gauge ?!
  if ( $q->count() == 0 )
    return $this->jsonError('Sorry, finding back orphans with the given parameters is impossible...');
  
  $this->json['success']['orphans'] = array(); // for the json data
  $orphans = array(); // for the message
  
  // gauges, one by one
  $cpt = 0;
  foreach ( $q->execute() as $gauge )
  {
    // preparing the field
    $seater = new Seater($gauge->id);
    $seats = new Doctrine_Collection('Seat');
    foreach ( $gauge->Tickets as $ticket )
      $seats[] = $ticket->Seat;
    
    // forging the json data
    foreach ( $seater->findOrphansWith($seats) as $orphan )
    {
      $this->json['success']['orphans'][] = array(
        'id'   => $orphan->id,
        'name' => (string)$orphan,
        'gauge_id' => $gauge->id,
        'manifestation_id' => $gauge->manifestation_id,
        'seated_plan_id' => $orphan->seated_plan_id,
        'transaction_id' => $this->getUser()->getTransaction()->id,
        'gauge' => (string)$gauge,
        'manifestation' => (string)$gauge->Manifestation,
      );
      
      // the message
      $orphans[] = (string)$orphan.' / '.$gauge->Manifestation;
    }
  }
  
  if ( $cpt = 0 )
    $this->json['success']['message'] = __('Perfect, no orphans found!');
  else
    $this->json['success']['message'] = __('You need to do something to avoid those orphans (%%orphans%%)...', array('%%orphans%%' => implode(', ',$orphans)));
  
  return 'Success';
