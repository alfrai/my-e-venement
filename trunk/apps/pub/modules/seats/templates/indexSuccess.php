<?php
  $seats = array();
  
  foreach ( $seated_plan->Seats as $seat )
  if (!(
       isset($occupied)
    && isset($occupied[$seat->id])
    && $occupied[$seat->id]['transaction_id'] === false
  ))
    $seats[] = array(
      'type'      => 'seat',
      'position'  => array(
        'x'         => $seat->x,
        'y'         => $seat->y,
      ),
      'diameter'  => $seat->diameter,
      'name'      => $seat->name,
      'id'        => $seat->id,
      'class'     => $seat->class,
      'rank'      => $seat->rank,
      'seated_plan_id' => $seated_plan->id,
      'occupied'  => isset($occupied) && isset($occupied[$seat->id]) ? $occupied[$seat->id] : false,
    );
  
  echo json_encode($seats);
