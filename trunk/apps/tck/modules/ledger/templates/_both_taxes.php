<?php if ( $taxes->count() > 0 ): ?>
<div class="ui-widget-content ui-corner-all" id="byTax">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h2>
      <?php echo __('Extra taxes') ?>
    </h2>
  </div>
<table>
<tbody>
<?php $qty = $total = array('in' => 0, 'out' => 0); $class = false; ?>
<?php foreach ( $taxes as $tax ): ?>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name"><?php echo $tax ?></td>
    <td class="value amount"><?php echo $tax->type == 'percentage' ? $tax->value.'%' : format_currency($tax->value, '€') ?></td>
    <td class="qty amount"><?php echo $tax->qty_out; $qty['out'] += $tax->qty_out; ?></td>
    <td class="outcomes amount"><?php echo format_currency($tax->amount_out,'€'); $total['out'] += $tax->amount_out; ?></td>
    <td class="qty amount"><?php echo $tax->qty_in; $qty['in'] += $tax->qty_in; ?></td>
    <td class="incomes amount"><?php echo format_currency($tax->amount_in,'€'); $total['in'] += $tax->amount_in; ?></td>
    <td class="qty amount"><?php echo $tax->qty_in - $tax->qty_out; ?></td>
    <td class="incomes amount"><?php echo format_currency($tax->amount_in+$tax->amount_out,'€') ?></td>
  </tr>
<?php endforeach ?>
<tbody>
<tfoot>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name">Total</td>
    <td class="value amount">-</td>
    <td class="qty amount"><?php echo $qty['out'] ?></td>
    <td class="outcomes amount"><?php echo format_currency($total['out'], '€') ?></td>
    <td class="qty amount"><?php echo $qty['in'] ?></td>
    <td class="incomes amount"><?php echo format_currency($total['in'], '€') ?></td>
    <td class="qty amount"><?php echo $qty['in']-$qty['out'] ?></td>
    <td class="incomes amount"><?php echo format_currency($total['in']+$total['out'], '€') ?></td>
  </tr>
</tfoot>
<thead>
  <tr>
    <td class="name"><?php echo __('Tax') ?></td>
    <td class="nb"><?php echo __('Value') ?></td>
    <td class="qty"><?php echo __('Nb') ?></td>
    <td class="outcomes"><?php echo __('Outcomes') ?></td>
    <td class="qty"><?php echo __('Nb') ?></td>
    <td class="incomes"><?php echo __('Incomes') ?></td>
    <td class="qty"><?php echo __('Nb') ?></td>
    <td class="outcomes"><?php echo __('Total') ?></td>
  </tr>
</thead>
</table>

</div>
<?php endif ?>
