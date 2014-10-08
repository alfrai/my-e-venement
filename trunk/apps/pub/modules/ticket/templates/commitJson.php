<?php
  $json = array();
  foreach ( $sf_user->getTransaction()->Tickets as $ticket )
  {
    if ( !isset($json[$ticket->gauge_id]) )
      $json[$ticket->gauge_id] = array();
    if ( !isset($json[$ticket->gauge_id][$ticket->price_id]) )
      $json[$ticket->gauge_id][$ticket->price_id] = 0;
    $json[$ticket->gauge_id][$ticket->price_id]++;
  }
?>
<?php if ( sfConfig::get('sf_web_debug', false) ): ?>
  <?php print_r($json) ?>
<?php else: ?>
  <?php echo json_encode($json) ?>
<?php endif ?>
