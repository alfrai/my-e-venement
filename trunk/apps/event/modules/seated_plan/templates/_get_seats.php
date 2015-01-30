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
  foreach ( $seated_plans as $seated_plan )
  foreach ( $seated_plan->Seats as $seat )
  {
    $held = false;
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
      if ( ($sf_request->hasParameter('gauges_list') || $sf_request->hasParameter('gauge_id'))
        && ($hold_id = $seat->isHeldFor($seated_plan->Workspaces[0]->Gauges[0]->Manifestation)) )
      {
        if ( $sf_request->hasParameter('ticketting') )
          continue(2);
        elseif ( $hold_id != $sf_request->getParameter('hold_id', NULL) )
          continue(2);
        $held = true;
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
      'id'        => $seat->id,
      'class'     => $seat->class.($held ? ' held' : ''),
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
