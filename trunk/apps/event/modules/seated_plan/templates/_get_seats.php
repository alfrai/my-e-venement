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
  $seats = array();
  $users = Doctrine::getTable('sfGuardUser')->createQuery('u')
    ->andWhereIn('u.username', sfConfig::get('app_manifestation_online_users', array()))
    ->execute();
  
  // preparing stuff to optimize fetching Seats from seated plans
  $prepare = array();
  $seated_plans_gauges = new Doctrine_Collection('Gauge');
  foreach ( $seated_plans as $sp )
  {
    $prepare[] = '?';
    $seated_plans_gauges[$sp->id] = $sp->Workspaces[0]->Gauges[0];
  }
  
  // optimized Seats fetching
  //$seat_records = new Doctrine_Collection('Seat');
  //foreach ( $seated_plans as $seated_plan )
  //  $seat_records->merge($seated_plan->Seats);
  $q = Doctrine::getTable('Seat')->createQuery('s')
    ->leftJoin('s.Holds h')
    ->leftJoin('s.SeatedPlan sp WITH sp.id IN ('.implode(',', $prepare).')', $seated_plans_gauges->getKeys())
  ;
  if ( $sf_request->getParameter('transaction_id', false) )
    $q->leftJoin('h.HoldTransactions ht WITH ht.transaction_id = ?', $sf_request->getParameter('transaction_id'))
      ->andWhere('sp.id IS NOT NULL OR ht.id IS NOT NULL');
  else
    $q->andWhere('sp.id IS NOT NULL');
  $seat_records = $q->execute();
  
  foreach ( $seat_records as $seat )
  {
    $spid = $seat->seated_plan_id;
    
    // especially for controlled tickets
    if ( !isset($type) )
      $type = 'seat';
    
    switch ( $type ) {
    case 'controls':
      if ( !isset($occupied[$seat->name]) )
        continue(2);
      break;
    case 'holds':
    case 'seat':
      if ( isset($occupied[$seat->name]) && $occupied[$seat->name]['type'] == 'out' )
        continue(2);
      
      // this is a trick for seats that are out of the current SeatedPlan but inside any current Hold
      if ( !isset($seated_plans_gauges[$seat->seated_plan_id]) && $sf_request->hasParameter('gauge_id') )
      foreach ( $seated_plans_gauges as $spid => $val )
        break;
      
      if ( ($sf_request->hasParameter('gauges_list') || $sf_request->hasParameter('gauge_id'))
        && ($hold = $seat->isHeldFor($seated_plans_gauges[$spid]->Manifestation )) )
      {
        $q = Doctrine::getTable('HoldTransaction')->createQuery('ht')
          ->leftJoin('ht.Transaction t')
          ->andWhere('t.id = ?', $sf_request->getParameter('transaction_id'))
          ->leftJoin('ht.Hold h')
          ->leftJoin('h.Manifestation m')
          ->leftJoin('m.Gauges g')
          ->andWhereIn('g.id', $seated_plans_gauges->getPrimaryKeys())
        ;
        $ht = $q->fetchOne();
        
        // Inside a ticketting process
        if ( $sf_request->hasParameter('ticketting') && !$ht )
          continue(2);
        // inside a Hold
        elseif ( $sf_request->hasParameter('hold_id', NULL) && $hold->id != $sf_request->getParameter('hold_id', NULL) )
          continue(2);
      }
      break;
    }
    
    $seats[] = array(
      'type'      => isset($type) ? $type : 'seat',
      'position'  => array(
        'x'         => $seat->x,
        'y'         => $seat->y,
      ),
      'diameter'  => $seat->diameter,
      'name'      => $seat->name,
      'info'      => isset($hold) && $hold ? __('Held for "%%hold%%"', array('%%hold%%' => $hold)) : NULL,
      'id'        => $seat->id,
      'class'     => $seat->class.(isset($hold) && $hold ? ' held hold-'.$hold->id : '').($seated_plans_gauges[$spid]->isAccessibleBy($users, true) ? '' : ' offline'),
      'rank'      => $seat->rank,
      'seated-plan-id' => $seat->seated_plan_id,
      'occupied'  => $sf_user->hasCredential('event-seats-allocation') && !(isset($occupied[$seat->name]) && $occupied[$seat->name]['type'] == 'out')
        ? (isset($occupied) && isset($occupied[$seat->name]) ? $occupied[$seat->name] : false)
        : array('type' => 'not-allowed'),
    );
  }
  
  if ( sfConfig::get('sf_web_debug', false) )
    echo '<pre>'.print_r($seats,true).'</pre>';
  else
    echo json_encode($seats);
