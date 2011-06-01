#<?php echo $sf_user->hasCredential('tck-transaction')
  ? link_to($invoice->transaction_id,'ticket/sell?id='.$invoice->transaction_id)
  : $invoice->transaction_id ?>
