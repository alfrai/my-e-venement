<?php if ( $taxes->count() > 0 ): ?>
<div class="ui-widget-content ui-corner-all" id="byTax">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h2>
      <?php echo __('Extra taxes') ?>
    </h2>
  </div>
<table>
<tbody>
<?php $qty = $total = 0; $class = false; ?>
<?php foreach ( $taxes as $tax ): ?>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name"><?php echo $tax ?></td>
    <td class="value amount"><?php echo $tax->type == 'percentage' ? $tax->value.'%' : format_currency($tax->value, '€') ?></td>
    <td class="qty amount"><?php echo $tax->qty; $qty += $tax->qty; ?></td>
    <td class="incomes amount"><?php echo format_currency($tax->amount,'€'); $total += $tax->amount; ?></td>
  </tr>
<?php endforeach ?>
<tbody>
<tfoot>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name">Total</td>
    <td class="value amount">-</td>
    <td class="qty amount"><?php echo $qty ?></td>
    <td class="incomes amount"><?php echo format_currency($total, '€') ?></td>
  </tr>
</tfoot>
<thead>
  <tr>
    <td class="name"><?php echo __('Tax') ?></td>
    <td class="nb"><?php echo __('Value') ?></td>
    <td class="qty"><?php echo __('Quantity') ?></td>
    <td class="incomes"><?php echo __('Incomes') ?></td>
  </tr>
</thead>
</table>

</div>
<?php endif ?>
