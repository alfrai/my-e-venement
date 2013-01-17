<?php
  $tickets = array('asked' => 0, 'ordered' => 0, 'printed' => 0, 'booked' => 0, 'total' => 0);
  
  if ( $manifestation->Gauges->count() > 0 && !isset($manifestation->Gauges[0]->printed) )
    $manifestation->Gauges = Doctrine::getTable('Gauge')->createQuery('g')
      ->andWhere('g.manifestation_id = ?',$manifestation->id)
      ->execute();
  
  foreach ( $manifestation->Gauges as $gauge )
  {
    $tickets['total']   += $gauge->value;
    $tickets['asked']   += $gauge->asked;
    $tickets['ordered'] += $gauge->ordered;
    $tickets['printed'] += $gauge->printed;
    $tickets['booked']  += $gauge->ordered + $gauge->printed;
    if ( sfConfig::get('project_tickets_count_demands',false) )
      $tickets['booked'] += $gauge->asked;
  }
?>
<?php if ( sfConfig::get('project_tickets_count_demands',false) ): ?>
<?php echo __('<strong class="booked">%%b%%</strong>/<strong>%%t%%</strong> (<span title="sold">%%p%%</span>-<span title="ordered">%%o%%</span>-<span title="asked">%%a%%</span>)', array(
    '%%p%%' => $tickets['printed'],
    '%%o%%' => $tickets['ordered'],
    '%%a%%' => $tickets['asked'],
    '%%b%%' => $tickets['booked'],
    '%%t%%' => $tickets['total'],
  )) ?>
<?php else: ?>
<?php echo __('<strong class="booked">%%b%%</strong>/<strong>%%t%%</strong> (<span title="sold">%%p%%</span>-<span title="ordered">%%o%%</span>)', array(
    '%%p%%' => $tickets['printed'],
    '%%o%%' => $tickets['ordered'],
    '%%b%%' => $tickets['booked'],
    '%%t%%' => $tickets['total'],
  )) ?>
<?php endif ?>
