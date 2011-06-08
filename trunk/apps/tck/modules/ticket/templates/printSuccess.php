<?php use_stylesheet('tickets.default') ?>
<?php if ( sfConfig::has('app_tickets_control_left') ) use_stylesheet('tickets.controlleft') ?>
<?php foreach ( $tickets as $ticket ): ?>
  <div class="page">
  <?php include_partial('ticket_html',array('ticket' => $ticket, 'duplicate' => $duplicate)) ?>
  </div>
<?php endforeach ?>
<div id="options">
  <?php if ( sfConfig::get('app_tickets_auto_close') ): ?>
  <p id="close"></p>
  <?php endif ?>
</div>
