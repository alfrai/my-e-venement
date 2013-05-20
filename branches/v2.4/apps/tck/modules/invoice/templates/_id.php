#<?php echo $sf_user->hasCredential('tck-transaction')
  ? link_to(sfConfig::get('app_seller_invoice_prefix').$invoice->id,'ticket/invoice?id='.$invoice->transaction_id,array('target' => '_blank'))
  : sfConfig::get('app_seller_invoice_prefix').$invoice->id ?>
