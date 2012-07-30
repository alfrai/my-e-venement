#<?php echo $sf_user->hasCredential('tck-transaction')
  ? link_to($order->id,'ticket/order?id='.$order->transaction_id)
  : $order->id ?>
