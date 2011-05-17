<?php include_partial('assets') ?>

<?php foreach ( $transactions as $transaction ): ?>
<?php if ( $transaction->topay > $transaction->paid ): ?>
<p>
<?php echo $transaction->id ?>
<br/>
<?php echo $transaction->topay ?>
<br/>
<?php echo $transaction->paid ?>
</p>
<?php endif ?>
<?php endforeach ?>
