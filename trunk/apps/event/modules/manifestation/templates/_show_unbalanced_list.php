<h2><?php echo __('Unbalanced transactions') ?></h2>

<?php if ( count($form->unbalanced) > 0 ): ?>
<table>
  <tbody>
    <?php $diff = 0 ?>
    <?php foreach ( $form->unbalanced as $tr ): ?>
    <tr>
      <td>#<?php echo cross_app_link_to($tr['id'],'tck',$tr['type'] == 'cancellation' ? 'ticket/pay?id='.$tr['id'] : 'ticket/sell?id='.$tr['id']) ?></td>
      <td class="nb"><?php echo format_currency($tr['topay'],'€') ?></td>
      <td class="nb"><?php echo format_currency($tr['paid'],'€') ?></td>
      <td class="nb"><?php echo format_currency($tr['topay'] - $tr['paid'],'€'); $diff += $tr['topay'] - $tr['paid']; ?></td>
    </tr>
    <?php endforeach ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="3"><?php echo __('Total debt') ?></td>
      <td class="nb"><?php echo format_currency($diff,'€') ?></td>
    </tr>
  </tfoot>
  <thead>
    <tr>
      <td><?php echo __('Transaction') ?></td>
      <td><?php echo __('Total to pay') ?></td>
      <td><?php echo __('Total paid') ?></td>
      <td><?php echo __('Debt') ?></td>
    </tr>
  </thead>
</table>
<p><?php echo __("Notice: all the amounts shown here are mixing all the tickets and all the payments of transactions linked to this manifestation") ?></p>
<?php else: ?>
<p>
  <strong><?php echo __('All the transactions linked to this manifestation are balanced') ?></strong>
</p>
<?php endif ?>

