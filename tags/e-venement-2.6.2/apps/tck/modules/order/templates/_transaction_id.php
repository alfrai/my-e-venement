#<?php echo $sf_user->hasCredential('tck-transaction')
  ? link_to($order->transaction_id,'ticket/sell?id='.$order->transaction_id)
  : $order->transaction_id ?>
