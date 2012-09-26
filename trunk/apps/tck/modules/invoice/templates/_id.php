#<?php echo $sf_user->hasCredential('tck-transaction')
  ? link_to($invoice->id,'ticket/invoice?id='.$invoice->transaction_id,array('target' => '_blank'))
  : $invoice->id ?>
