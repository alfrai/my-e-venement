<?php $cpt = 0 ?>
<?php foreach ( $tickets as $ticket ): ?>
  <?php $cpt++ ?>
  <?php // already seated ?>
  <span class="seat seat-<?php echo slugify($ticket->Seat) ?>" data-seat-id="<?php echo $ticket->seat_id ?>" data-ticket-id="<?php echo $ticket->id ?>">
    <?php echo $ticket->Seat ?>
    <input
      type="hidden"
      name="<?php echo sprintf($form->getWidgetSchema()->getNameFormat(), 'seat_id') ?>[]"
      value="<?php echo $ticket->seat_id ?>"
    />
  </span>
<?php endforeach ?>
<?php if ( $cpt > 0 ): ?>
<?php error_log($cpt.' tickets from price '.$ticket->price_name.' for gauge '.$ticket->gauge_id) ?>
<?php endif ?>
