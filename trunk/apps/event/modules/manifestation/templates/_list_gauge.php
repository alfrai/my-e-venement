<?php
  $tickets = array('asked' => 0, 'ordered' => 0, 'printed' => 0, 'booked' => 0, 'total' => 0);
  
  foreach ( $manifestation->Gauges as $gauge )
    $tickets['total'] += $gauge->value;
  
  if ( $tickets['total'] < 7500 )
  {
    $cancelled = array();
    foreach ( $manifestation->Tickets as $ticket )
    if ( $ticket->Duplicatas->count() == 0 )
    {
      if ( is_null($ticket->cancelling) )
      {
        $tickets[!$ticket->printed && !$ticket->integrated ? ($ticket->Transaction->Order->count() > 0 ? 'ordered' : 'asked') : 'printed']++;
        if ( sfConfig::get('project_tickets_count_demands',false) || $ticket->printed || $ticket->integrated || $ticket->Transaction->Order->count() > 0 )
          $tickets['booked']++;
      }
      else if ( !in_array($ticket->cancelling, $cancelled) )
      {
        $cancelled[] = $ticket->cancelling;
        $tickets['printed']--;
        $tickets['booked']--;
      }
    }
  }
  else
    $tickets = array('asked' => 'N/A', 'ordered' => 'N/A', 'printed' => 'N/A', 'booked' => 'N/A', 'total' => $tickets['total']);
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
