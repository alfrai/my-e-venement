<ul class="transaction">
  <?php $printed = false; foreach ( $transaction->Tickets as $ticket ) if ( !is_null($ticket->printed_at) || !is_null($ticket->integrated_at) ) { $printed = true; break; } ?>
  <?php if ( $printed ): ?>
  <li class="confirmation"><?php echo __('Purchase confirmed') ?></li>
  <?php endif ?>
  <?php if ( $transaction->Order->count() > 0 ): ?>
  <li class="payment"><?php echo $transaction->getPrice(true, true).'' <= ''.$transaction->getPaid() ? __('Order paid') : __('Payment in progress') ?></li>
  <?php endif ?>
  <?php $get_tickets = true ?>
  <?php if ( $transaction->Order->count() == 0 && !$printed ): ?>
  <?php $get_tickets = false ?>
  <li class="state"><?php echo __('In progress...') ?></li>
  <?php endif ?>
</ul>
