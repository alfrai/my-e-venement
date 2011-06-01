#<?php echo $sf_user->hasCredential('tck-transaction')
  ? link_to($transaction->id,'ticket/sell?id='.$transaction->id)
  : $transaction->id ?>
