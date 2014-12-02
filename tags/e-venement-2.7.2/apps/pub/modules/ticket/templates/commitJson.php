<?php
  $json = $json->getRawValue();
  $json['tickets'] = array();
  foreach ( $sf_user->getTransaction()->Tickets as $ticket )
  {
    if ( !isset($json['tickets'][$ticket->gauge_id]) )
      $json['tickets'][$ticket->gauge_id] = array();
    if ( !isset($json['tickets'][$ticket->gauge_id][$ticket->price_id]) )
      $json['tickets'][$ticket->gauge_id][$ticket->price_id] = 0;
    $json['tickets'][$ticket->gauge_id][$ticket->price_id]++;
  }
?>
<?php if ( sfConfig::get('sf_web_debug', false) ): ?>
  <?php print_r($json) ?>
<?php else: ?>
  <?php echo json_encode($json) ?>
<?php endif ?>
