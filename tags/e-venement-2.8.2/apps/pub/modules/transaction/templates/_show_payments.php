<table>
  <tbody>
    <?php $total = 0 ?>
    <?php foreach ( $transaction->Payments as $payment ): ?>
    <tr>
      <td class="method"><?php echo $payment->Method ?></td>
			<td class="date"><?php echo format_date($payment->created_at) ?></td>
      <td class="value"><?php echo format_currency($payment->value,'€'); $total += $payment->value ?></td>
    </tr>
    <?php endforeach ?>
  </tbody>
  <tfoot>
    <tr>
      <td class="method"><?php echo __('Total') ?></td>
			<td class="date"></td>
      <td class="value"><?php echo format_currency($total,'€') ?></td>
		</tr>
  <thead>
    <tr>
      <td class="method"><?php echo __('Payment method') ?></td>
			<td class="date"><?php echo __('Date') ?></td>
      <td class="value"><?php echo __('Amount') ?></td>
    </tr>
  </thead>
</table>
