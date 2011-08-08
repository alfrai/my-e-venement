<?php
  $tickets = array('asked' => 0, 'ordered' => 0, 'printed' => 0, 'booked' => 0, 'total' => 0);
  
  foreach ( $manifestation->Gauges as $gauge )
    $tickets['total'] += $gauge->value;
  
  foreach ( $manifestation->Tickets as $ticket )
  if ( is_null($ticket->cancelling) && is_null($ticket->duplicate) )
  {
    $tickets[$ticket->printed ? 'printed' : $ticket->Transaction->Order->count() > 0 ? 'ordered' : 'asked']++;
    $tickets['booked']++;
  }
?>
<?php echo __('<strong class="booked">%%b%%</strong>/<strong>%%t%%</strong> (<span title="sold">%%p%%</span>-<span title="ordered">%%o%%</span>-<span title="asked">%%a%%</span>)', array(
    '%%p%%' => $tickets['printed'],
    '%%o%%' => $tickets['ordered'],
    '%%a%%' => $tickets['asked'],
    '%%b%%' => $tickets['booked'],
    '%%t%%' => $tickets['total'],
  )) ?>
