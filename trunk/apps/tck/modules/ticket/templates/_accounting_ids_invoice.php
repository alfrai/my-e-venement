<p id="ids" class="invoice">
  <?php echo __('<span class="name">Invoice</span> #%%prefix%%<span class="invoice_id">%%iid%%</span><span class="transaction">, for transaction #<span class="transaction_id">%%tid%%</span> from the <span class="date">%%d%%</span></span>',array('%%d%%' => format_date(strtotime($transaction->created_at)), '%%iid%%' => $invoice->id, '%%tid%%' => $transaction->id, '%%prefix%%' => sfConfig::get('app_seller_invoice_prefix'))) ?>
</p>
