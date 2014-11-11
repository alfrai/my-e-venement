<div id="actions">
<?php if ( $transaction->id == $sf_user->getTransactionId() ): ?>
<div class="actions index">
<?php echo link_to(__('Continue shopping'),'event/index') ?>
</div>
<div class="actions register">
<?php echo link_to(__('Checkout'),'cart/register') ?>
</div>
<div class="actions empty">
<?php echo link_to(__('Empty your basket'),'cart/empty') ?>
</div>
<?php else: ?>
<div class="actions register">
<?php echo link_to(__('Payment'),'cart/register?transaction_id='.$transaction->id) ?>
</div>
<?php endif ?>
</div>
