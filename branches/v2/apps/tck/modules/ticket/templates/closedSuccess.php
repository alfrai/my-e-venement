<?php use_helper('CrossAppLink') ?>
<p><?php echo __('Transaction #<a href="%%url%%">%%tid%%</a>',array(
  '%%tid%%' => $transaction->id,
  '%%url%%' => url_for('ticket/sell?id='.$transaction),
)) ?></p>
<p>
  <?php echo __('Thanks <a href="%%url%%">%%t%% %%f%% %%n%%</a>',array(
    '%%t%%' => $transaction->Contact->title,
    '%%f%%' => $transaction->Contact->firstname,
    '%%n%%' => $transaction->Contact->name,
    '%%url%%' => cross_app_url_for('rp','contact/show?id='.$transaction->Contact->id),
  )) ?>
</p>
<p>
  <a href="<?php echo url_for('ticket/sell') ?>"><?php echo __('Start a new opertation') ?></a>
</p>
