<h2><?php echo __('Unbalanced transactions') ?></h2>

<?php if ( count($form->unbalanced) > 0 ): ?>
<table>
  <tbody>
    <?php $diff = 0 ?>
    <?php foreach ( $form->unbalanced as $tr ): ?>
    <tr>
      <td class="transaction" title="<?php if ( $tr['transaction_id'] ) echo '#'.$tr['transaction_id']; else echo isset($tr['translinked']) && $tr['translinked'] ? '#'.$tr['translinked'] : ''; ?>">#<?php echo cross_app_link_to($tr['id'],'tck',$tr['type'] == 'cancellation' ? 'ticket/pay?id='.$tr['id'] : 'ticket/sell?id='.$tr['id']) ?></td>
      <td class="contact" title="<?php echo $tr['firstname'].' '.$tr['name'].' '.__('@').' '.$tr['o_name'].' ('.$tr['o_city'].')' ?>">
        <?php echo cross_app_link_to($tr['firstname'].' '.$tr['name'],'rp','contact/show?id='.$tr['c_id']) ?>
        <?php echo __('@') ?>
        <?php echo cross_app_link_to($tr['o_name'],'rp','organism/show?id='.$tr['o_id']) ?> (<?php echo $tr['o_city'] ?>)
      </td>
      <td class="nb topay"><?php echo format_currency($tr['topay'],'€') ?></td>
      <td class="nb paid"><?php echo format_currency($tr['paid'],'€') ?></td>
      <td class="nb total"><?php echo format_currency($tr['topay'] - $tr['paid'],'€'); $diff += $tr['topay'] - $tr['paid']; ?></td>
    </tr>
    <?php endforeach ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="4" class="total"><?php echo __('Total debt') ?></td>
      <td class="nb"><?php echo format_currency($diff,'€') ?></td>
    </tr>
  </tfoot>
  <thead>
    <tr>
      <td class="transaction"><?php echo __('Transaction') ?></td>
      <td class="contact"><?php echo __('Contact') ?></td>
      <td class="topay"><?php echo __('Total to pay') ?></td>
      <td class="paid"><?php echo __('Total paid') ?></td>
      <td class="total"><?php echo __('Debt') ?></td>
    </tr>
  </thead>
</table>
<p><?php echo __("Notice: all the amounts shown here are mixing all the tickets and all the payments of transactions linked to this manifestation") ?></p>
<?php else: ?>
<p>
  <strong><?php echo __('All the transactions linked to this manifestation are balanced') ?></strong>
</p>
<?php endif ?>
