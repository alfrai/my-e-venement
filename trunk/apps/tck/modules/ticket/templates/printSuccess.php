<?php if ( sfConfig::has('app_tickets_control_left') ) use_stylesheet('print-tickets.controlleft.css', '', array('media' => 'all')) ?>
<?php foreach ( $tickets as $ticket ): ?>
  <div class="page">
  <?php include_partial('ticket_html',array('ticket' => $ticket, 'duplicate' => $duplicate)) ?>
  </div>
<?php endforeach ?>
<div id="options">
  <?php if ( sfConfig::get('app_tickets_auto_close') ): ?>
  <p id="close"></p>
  <?php endif ?>
  <?php if ( $print_again ): ?>
  <p id="print-again"><a target="_blank" href="<?php echo url_for('ticket/print?'.
    'manifestation_id='.$manifestation_id.
    '&id='.$transaction->id.
    (isset($duplicate) && $duplicate ? '&duplicate=duplicate' : '').
    (isset($toprint) && $toprint ? '&toprint[]='.implode('&toprint[]=',$toprint) : '')
  ) ?>">&nbsp;</a></p>
  <?php endif ?>
</div>
