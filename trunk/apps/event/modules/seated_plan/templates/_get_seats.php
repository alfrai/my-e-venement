<?php
  $seats = array();
  foreach ( $seated_plans as $seated_plan )
  foreach ( $seated_plan->Seats as $seat )
  {
    // especially for controlled tickets
    if ( isset($type) && !isset($occupied[$seat->name]) )
      continue;
    $seats[] = array(
      'type'      => isset($type) ? $type : 'seat',
      'position'  => array(
        'x'         => $seat->x,
        'y'         => $seat->y,
      ),
      'diameter'  => $seat->diameter,
      'name'      => $seat->name,
      'id'        => $seat->id,
      'class'     => $seat->class,
      'rank'      => $seat->rank,
      'seated-plan-id' => $seat->seated_plan_id,
      'occupied'  => $sf_user->hasCredential('event-seats-allocation')
        ? (isset($occupied) && isset($occupied[$seat->name]) ? $occupied[$seat->name] : false)
        : array('type' => 'not-allowed'),
    );
  }
  
  if ( sfConfig::get('sf_web_debug', false) )
    echo '<pre>'.print_r($seats,true).'</pre>';
  else
    echo json_encode($seats);
