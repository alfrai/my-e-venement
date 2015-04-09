<ul>
<?php foreach ( $contact->Transactions as $transaction ): ?>
<?php if ( $transaction->type != 'cancellation' ): ?>
<?php if ( count(array_filter($transaction->getRawValue()->Tickets->toKeyValueArray('id', 'printed_at'))) + count(array_filter($transaction->getRawValue()->Tickets->toKeyValueArray('id', 'integrated_at'))) > 0 ): ?>
  <?php
    $printed = false;
    foreach ( $transaction->Tickets as $ticket )
    if ( $ticket->printed_at || $ticket->integrated_at )
      $printed = true;
  ?>
  <li class="<?php if ( !$printed ) echo 'not-printed'; ?> <?php if ( $transaction->Order->count() > 0 ) echo 'ordered'; ?>"
    title="<?php if ( $transaction->Order->count() > 0 ) echo __('Order').' #'.$transaction->Order[0]->id ?>"
  >#<?php echo cross_app_link_to($transaction->id, 'tck', 'transaction/edit?id='.$transaction->id) ?></li>
<?php endif ?>
<?php endif ?>
<?php endforeach ?>
</ul>
