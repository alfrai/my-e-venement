<?php
  $seats = array();
  
  foreach ( $seated_plan->Seats as $seat )
  if (!( isset($occupied) && isset($occupied[$seat->id]) ))
    $seats[] = array(
      'position' => array(
        'x'         => $seat->x,
        'y'         => $seat->y,
      ),
      'diameter'  => $seat->diameter,
      'name'      => $seat->name,
      'id'        => $seat->id,
      'rank'      => $seat->rank,
    );
  
  echo json_encode($seats);
