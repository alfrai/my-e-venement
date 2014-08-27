<div class="ui-widget-content ui-corner-all" id="byPaymentMethod">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h2>
      <?php echo __("Payment modes") ?>
    </h2>
  </div>
  <?php if ( is_array($form->getValue('manifestations')) && count($form->getValue('manifestations')) > 0 ): ?>
  <?php endif ?>
<table>
<tbody>
<?php $total = array('nb' => 0, 'value+' => 0, 'value-' => 0); $class = false; ?>
<?php foreach ( $byPaymentMethod as $pm ): ?>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name"><?php echo $pm['name'] ?></td>
    <td class="nb"><?php echo $pm['nb']; $total['nb'] += $pm['nb'] ?></td>
    <td class="outcomes amount"><?php echo format_currency($pm['value-'],'€'); $total['value+'] += $pm['value+']; ?></td>
    <td class="incomes amount"><?php echo format_currency($pm['value+'],'€'); $total['value-'] += $pm['value-'] ?></td>
    <td class="total"><?php echo format_currency($pm['value-']+$pm['value+'],'€'); ?></td>
  </tr>
<?php endforeach ?>
<tbody>
<tfoot>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name">Total</td>
    <td class="nb"><?php echo $total['nb'] ?></td>
    <td class="outcomes amount"><?php echo format_currency($total['value-'],'€') ?></td>
    <td class="incomes amount"><?php echo format_currency($total['value+'],'€') ?></td>
    <td class="total"><?php echo format_currency($total['value+']+$total['value-'],'€') ?></td>
  </tr>
</tfoot>
<thead>
  <tr>
    <td class="name"><?php echo __('Entitled') ?></td>
    <td class="nb"><?php echo __('Number') ?></td>
    <td class="outcomes"><?php echo __('Outcomes') ?></td>
    <td class="incomes"><?php echo __('Incomes') ?></td>
    <td class="total"><?php echo __('Total') ?></td>
  </tr>
</thead>
</table>

</div>
