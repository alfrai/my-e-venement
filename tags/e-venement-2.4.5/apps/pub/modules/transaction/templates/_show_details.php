<ul>
  <?php $printed = false; foreach ( $transaction->Tickets as $ticket ) if ( $ticket->printed ) { $printed = true; break; } ?>
  <?php if ( $printed ): ?>
	<li><?php echo __('Printed (event partially)') ?></li>
  <?php endif ?>
  <?php if ( $transaction->Order->count() > 0 ): ?>
  <li><?php echo __('Booked') ?></li>
  <?php endif ?>
  <?php if ( $transaction->Order->count() == 0 && !$printed ): ?>
  <li><?php echo __('In progress...') ?></li>
  <?php endif ?>
</ul>
