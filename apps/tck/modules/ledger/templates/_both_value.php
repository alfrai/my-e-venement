<div class="ui-widget-content ui-corner-all" id="byValue">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <?php include_partial('both_extract') ?>
    <h2><?php echo __("Summary of values") ?></h2>
  </div>

<table id="byValue">
<tbody>
<?php $total = array('nb' => 0, 'value' => 0, 'exo' => 0); $class = false; ?>
<?php foreach ( $byValue as $value ): ?>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name nb"><?php echo format_currency($value['value'],$sf_context->getConfiguration()->getCurrency()) ?></td>
    <td class="nb"><?php echo $value['nb']; $total['nb'] += $value['nb']; ?></td>
    <td class="total"><?php echo format_currency($value['total'],$sf_context->getConfiguration()->getCurrency()); $total['value'] += $value['total']; ?></td>
    <?php if ( $value['value'] == 0 ) $total['exo'] += $value['nb'] ?>
  </tr>
<?php endforeach ?>
</tbody>
<tfoot>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name">Total</td>
    <td class="nb"><?php echo $total['nb'] ?></td>
    <td class="total"><?php echo format_currency($total['value'],$sf_context->getConfiguration()->getCurrency()) ?></td>
  </tr>
</tfoot>
<thead>
  <tr>
    <td class="name"><?php echo __('Value') ?></td>
    <td class="nb"><?php echo __('Number') ?></td>
    <td class="total"><?php echo __('Total') ?></td>
  </tr>
</thead>
</table>

</div>

<div class="ui-widget-content ui-corner-all" id="byValueSynthesis">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <?php include_partial('both_extract') ?>
    <h2><?php echo __("Sales synthesis") ?></h2>
  </div>

<?php $class = false ?>
<table>
<tbody>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name"><?php echo __('Quantity of products') ?></td>
    <td class="value"><?php echo $total['nb'] ?></td>
    <td class="rating">100 %</td>
  </tr>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name"><?php echo __('Average price by product') ?></td>
    <td class="value"><?php echo $total['nb'] > 0 ? format_currency($total['value']/$total['nb'],$sf_context->getConfiguration()->getCurrency()) : 'N/A' ?></td>
    <td class="rating">-</td>
  </tr>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name"><?php echo __('Quantity of free products') ?></td>
    <td class="value"><?php echo $total['exo'] ?></td>
    <td class="rating"><?php echo $total['nb'] > 0 ? format_number($percent = round(100*$total['exo'] / $total['nb'],1)) : 'N/A' ?> %</td>
  </tr>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name"><?php echo __('Quantity of paid products') ?></td>
    <td class="value"><?php echo $total['nb']-$total['exo'] ?></td>
    <td class="rating"><?php echo isset($percent) ? format_number(round(100 - $percent,1)) : 'N/A' ?> %</td>
  </tr>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name"><?php echo __('Average price for paid products') ?></td>
    <td class="value"><?php echo $total['nb']-$total['exo'] > 0 ? format_currency($total['value'] / ($total['nb']-$total['exo']),$sf_context->getConfiguration()->getCurrency()) : 'N/A' ?></td>
    <td class="rating">-</td>
  </tr>
</tbody>
<thead>
  <tr>
    <td class="name"><?php echo __('Sales synthesis') ?></td>
    <td class="value"><?php echo __('Value') ?></td>
    <td class="rating"><?php echo __('Rating') ?></td>
  </tr>
</thead>
</table>

</div>
