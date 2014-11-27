<?php if ( $transaction->professional_id ): ?>
  <?php echo cross_app_link_to($transaction->Professional, 'rp', 'organism/edit?id='.$transaction->professional_id) ?>
<?php endif ?>
