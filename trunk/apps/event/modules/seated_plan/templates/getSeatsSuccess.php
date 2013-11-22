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
      'occupied'  => isset($occupied) && isset($occupied[$seat->name]) ? $occupied->getRaw($seat->name) : false,
    );
  
  echo json_encode($seats);
