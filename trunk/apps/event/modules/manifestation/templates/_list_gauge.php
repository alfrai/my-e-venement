<?php
  $tickets = array('asked' => 0, 'ordered' => 0, 'printed' => 0, 'free' => 0, 'total' => 0);
  
  foreach ( $manifestation->Gauges as $gauge )
    $tickets['total'] += $gauge->value;
  $tickets['free'] = $tickets['total'];
  
  foreach ( $manifestation->Tickets as $ticket )
  if ( is_null($ticket->cancelling) && is_null($ticket->duplicate) )
  {
    $tickets[$ticket->printed ? 'printed' : $ticket->Transaction->Order->count() > 0 ? 'ordered' : 'asked']++;
    $tickets['free']--;
  }
?>
<?php echo __('%%f%%/<strong>%%t%%</strong> (<span title="printed">%%p%%</span>-<span title="ordered">%%o%%</span>-<span title="asked">%%a%%</span>)', array(
    '%%p%%' => $tickets['printed'],
    '%%o%%' => $tickets['ordered'],
    '%%a%%' => $tickets['asked'],
    '%%f%%' => $tickets['free'],
    '%%t%%' => $tickets['total'],
  )) ?>
