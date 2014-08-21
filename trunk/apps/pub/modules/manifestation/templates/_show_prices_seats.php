<?php $cpt = 0 ?>
<?php foreach ( $tickets as $ticket ): ?>
<?php if ( $ticket->seat_id ): ?>
<?php $cpt++ ?>
  <?php // already seated ?>
  <span class="seat seat-<?php echo slugify($ticket->Seat) ?>" data-seat-id="<?php echo $ticket->seat_id ?>">
    <?php echo $ticket->Seat ?>
    <?php if ( $ticket->price_id ): ?>
      <input type="hidden" name="<?php echo sprintf($form->getWidgetSchema()->getNameFormat(), 'seat_id') ?>[]" value="<?php echo $ticket->seat_id ?>" />
    <?php endif ?>
  </span>
<?php else: ?>
  <?php // not yet seated ?>
  <span class="seat seat-todo">
    <?php echo $ticket ?>
  </span>
<?php endif ?>
<?php endforeach ?>
