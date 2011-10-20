<p id="ids" class="invoice">
  <?php echo __('<span class="name">Invoice</span> #%%prefix%%<span class="invoice_id">%%iid%%</span>, for transaction #<span class="transaction_id">%%tid%%</span>',array('%%iid%%' => $invoice->id, '%%tid%%' => $transaction->id, '%%prefix%%' => sfConfig::get('app_seller_invoice_prefix'))) ?>
</p>
