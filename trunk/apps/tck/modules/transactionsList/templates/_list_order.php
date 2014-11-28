<?php if ( $transaction->Order->count() > 0 ): ?>
  #<?php echo link_to($transaction->Order[0]->id, 'ticket/order?id='.$transaction->id) ?>
<?php endif ?>
