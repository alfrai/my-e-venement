<?php if ( sfConfig::has('app_tickets_control_left') ) use_stylesheet('print-tickets.controlleft.css', '', array('media' => 'all')) ?>
<?php foreach ( $tickets as $ticket ): ?>
  <div class="page">
  <?php include_partial('ticket_html',array(
    'ticket' => isset($ticket['ticket']) ? $ticket['ticket'] : $ticket,
    'nb' => isset($ticket['nb']) ? $ticket['nb'] : 1,
    'duplicate' => $duplicate)) ?>
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
    (isset($duplicate) && $duplicate ? '&duplicate=duplicate&price_name='.(isset($ticket['ticket']) ? $ticket['ticket']['price_name'] : $ticket->price_name) : '').
    (isset($grouped_tickets) && $grouped_tickets ? '&grouped_tickets=true' : '').
    (isset($toprint) && $toprint ? '&toprint[]='.implode('&toprint[]=',$toprint) : '')
  ) ?>">&nbsp;</a></p>
  <?php endif ?>
</div>
