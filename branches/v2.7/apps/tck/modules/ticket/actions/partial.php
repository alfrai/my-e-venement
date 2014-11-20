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
  $manifestations = $request->getParameter('manifestation_id', array());
  if ( !is_array($manifestations) ) $manifestations = array($manifestations);
  foreach ( $manifestations as $key => $value )
    $manifestations[$key] = intval($value);
  
  $gauges = $request->getParameter('gauge_id', array());
  if ( !is_array($gauges) ) $gauges = array($gauges);
  foreach ( $gauges as $key => $value )
    $gauges[$key] = intval($value);
  
  $this->transaction_id = intval($request->getParameter('id'));
  
  $q = Doctrine::getTable('Manifestation')->createQuery('m')
    ->leftJoin('m.Tickets tck')
    ->leftJoin('tck.Transaction t')
    ->andWhere('t.id = ?',$this->transaction_id)
    ->andWhere('tck.id NOT IN (SELECT tck2.duplicating FROM Ticket tck2 WHERE tck2.duplicating IS NOT NULL)')
    ->andWhere('tck.cancelling IS NULL')
    ->orderBy('m.happens_at, e.name, tck.price_name, tck.id');

  if ( $manifestations )
    $q->andWhereIn('m.id',$manifestations);
  if ( $gauges )
    $q->andWhereIn('tck.gauge_id',$gauges);
  
  $this->manifestations = $q->execute();
  
  if ( $this->manifestations->count() == 1 && $this->manifestations[0]->Tickets->count() == 1 )
    $this->redirect('ticket/print?id='.$this->transaction_id.'&manifestation_id='.$this->manifestations[0]->id);
  
  $gauges = array();
  foreach ( $this->manifestations as $manif )
  foreach ( $manif->Tickets as $ticket )
  if ( !$ticket->numerotation )
    $gauges[$ticket->gauge_id] = $ticket->gauge_id;
  if ( count($gauges) > 0 )
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->getUser()->setFlash('notice', __('You must seat all your tickets before print them, even partially'));
    $this->redirect('ticket/seatsAllocation?id='.$this->transaction_id.'&type=partial&gauge_id='.array_pop($gauges));
  }
