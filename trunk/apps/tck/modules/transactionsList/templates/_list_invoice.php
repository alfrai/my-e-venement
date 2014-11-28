<?php if ( $transaction->Invoice->count() > 0 ): ?>
  #<?php echo link_to($transaction->Invoice[0]->id, 'ticket/invoice?id='.$transaction->id) ?>
<?php endif ?>
