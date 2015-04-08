<?php use_helper('Number') ?>
<?php use_helper('Date') ?>
<?php $total = 0 ?>
<ul>
  <?php foreach ( $transaction->Payments as $payment ): ?>
    <li class="method-<?php echo $payment->payment_method_id ?>">
      <span class="value"><?php echo format_currency($payment->value,'€') ?></span>
      <?php $total += $payment->value ?>
      <ul>
        <li class="created_at"><?php echo format_date($payment->created_at) ?></li>
        <li class="method"><?php echo $payment->Method ?></li>
        <li class="user"><?php echo $payment->User ?></li>
      </ul>
    </li>
  <?php endforeach ?>
  <?php if ( $transaction->Payments->count() > 1 ): ?>
  <li class="total">
    <span class="value"><?php echo format_currency($total, '€') ?></span>
  </li>
  <?php endif ?>
</ul>
