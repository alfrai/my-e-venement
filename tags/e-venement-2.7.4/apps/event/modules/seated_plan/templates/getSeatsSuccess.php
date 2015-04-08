<?php
  $seats = array();
  
  foreach ( $seated_plan->Seats as $seat )
    $seats[] = array(
      'position' => array(
        'x'         => $seat->x,
        'y'         => $seat->y,
      ),
      'diameter'  => $seat->diameter,
      'name'      => $seat->name,
      'occupied'  => $sf_user->hasCredential('event-seats-allocation')
        ? (isset($occupied) && isset($occupied[$seat->name]) ? $occupied[$seat->name] : false)
        : array('type' => 'not-allowed'),
    );
  
  echo json_encode($seats);
