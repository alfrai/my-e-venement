<?php
  use_helper('Number');
  $prices = $seats = array();
  
  foreach ( $seated_plans as $seated_plan )
  foreach ( $seated_plan->Seats as $seat )
  if (!(
       isset($occupied)
    && isset($occupied[$seat->id])
    && $occupied[$seat->id]['transaction_id'] === false
  ))
  {
    if ( !isset($prices[$seated_plan->Workspaces[0]->Gauges[0]->id]) )
    {
      $prices[$seated_plan->Workspaces[0]->Gauges[0]->id] = array();
      foreach ( $seated_plan->Workspaces[0]->Gauges[0]->Manifestation->PriceManifestations as $pm )
      if ( $pm->Price->isAccessibleBy($sf_user) )
        $prices[$seated_plan->Workspaces[0]->Gauges[0]->id][$pm->price_id] = $pm->value;
      foreach ( $seated_plan->Workspaces[0]->Gauges[0]->PriceGauges as $pg )
      if ( $pg->Price->isAccessibleBy($sf_user) )
        $prices[$seated_plan->Workspaces[0]->Gauges[0]->id][$pg->price_id] = $pg->value;
    }
    
    $infos = array();
    $infos[] = $seated_plan->Workspaces[0]->Gauges[0]->group_name ? $seated_plan->Workspaces[0]->Gauges[0]->group_name : $seated_plan->Workspaces[0];
    if ( count($prices[$seated_plan->Workspaces[0]->Gauges[0]->id]) > 0 )
    $infos[] = min($prices[$seated_plan->Workspaces[0]->Gauges[0]->id]) != max($prices[$seated_plan->Workspaces[0]->Gauges[0]->id])
      ? __('from %%from%% to %%to%%', array(
        '%%from%%' => format_currency(min($prices[$seated_plan->Workspaces[0]->Gauges[0]->id]), '€'),
        '%%to%%'   => format_currency(max($prices[$seated_plan->Workspaces[0]->Gauges[0]->id]), '€'),
      ))
      : format_currency(min($prices[$seated_plan->Workspaces[0]->Gauges[0]->id]), '€')
    ;
    
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
      'rank'      => '',
      'info'      => implode(', ',$infos),
      'seated_plan_id' => $seated_plan->id,
      'occupied'  => isset($occupied) && isset($occupied[$seat->id]) ? $occupied[$seat->id] : false,
    );
  }
  
  if ( sfConfig::get('sf_web_debug', false) )
    echo '<pre>'.print_r($seats, true).'</pre>';
  else
    echo json_encode($seats);
