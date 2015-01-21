<?php use_helper('Number') ?>
<?php if ( $member_card->Payments->count() > 0 ): ?>
<?php $value = 0 ?>
<div class="sf_admin_form_row li-payments-list">
  <label><?php echo __('List of payments') ?>:</label>
  <table class="payments_list ui-widget ui-corner-all ui-widget-content">
  <tbody>
  <?php foreach ( $member_card->Payments as $payment ): ?>
    <tr>
      <td><?php echo cross_app_link_to('#'.$payment->transaction_id,'tck','ticket/pay?id='.$payment->transaction_id) ?></td>
      <td><?php echo $payment->Method ?></td>
      <td class="payment_value"><?php echo format_currency($payment->value,'€'); $value += $payment->value ?></td>
    </tr>
  <?php endforeach ?>
  </tbody>
  <tfoot>
    <tr>
      <td></td>
      <td></td>
      <td class="payment_value"><?php echo format_currency($value,'€') ?></td>
    </tr>
  </tfoot>
  </table>
</div>
<?php endif ?>
