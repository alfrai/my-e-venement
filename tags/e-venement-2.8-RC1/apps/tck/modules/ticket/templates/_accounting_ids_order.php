<p id="ids" class="order">
  <?php echo __('<span class="name inline-modifiable">Order</span> #<span class="order_id inline-modifiable">%%oid%%</span>, for transaction #<span class="transaction_id inline-modifiable">%%tid%%</span>',array('%%oid%%' => $order->id, '%%tid%%' => $transaction->id), 'li_accounting') ?>
</p>
