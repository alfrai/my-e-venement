  <span class="workspace gauge-<?php echo $ticket->gauge_id ?>">
    <?php if ( $nb_gauges > 1 ): ?>
      <span class="ws-gauge"><span class="url"><?php echo cross_app_url_for('event','gauge/state?id='.$ticket->gauge_id) ?></span></span>
    <?php endif ?>
  <?php /* </span> */ // trick for multiple gauges, see parent partial ?>
