<?php if ( $transaction->transaction_id ): ?>
#<?php echo link_to($transaction->transaction_id, 'transaction/edit?id='.$transaction->transaction_id) ?>
<?php endif ?>
<?php foreach ( $transaction->Translinked as $t ): ?>
<em>#<?php echo link_to($t->transaction_id, 'transaction/edit?id='.$t->transaction_id) ?> </em>
<?php endforeach ?>
