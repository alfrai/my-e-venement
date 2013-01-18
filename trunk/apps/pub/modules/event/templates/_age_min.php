<?php
  use_helper('Number');
  
  if ( isset($event->age_min) && $event->age_min > 0 )
  echo $event->age_min > 2
    ? __('%%i%% years old',array('%%i%%' => round($event->age_min)))
    : __('%%i%% month old',array('%%i%%' => round($event->age_min * 12)));
?>
