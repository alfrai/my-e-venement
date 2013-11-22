<?php
  $seats = array();
  
  foreach ( $seated_plan->Seats as $seat )
    $seats[] = array(
      'location' => array(
        'x'         => $seat->x,
        'y'         => $seat->y,
      ),
      'diameter'  => $seat->diameter,
      'name'      => $seat->name,
    );
  
  echo json_encode($seats);
