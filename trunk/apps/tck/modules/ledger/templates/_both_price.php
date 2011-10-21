<div class="ui-widget-content ui-corner-all" id="byPrice">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h2><?php echo __("Prices' summary") ?></h2>
  </div>

<table>
<tbody>
<?php $total = array('nb+' => 0, 'nb-' => 0, 'value+' => 0, 'value-' => 0) ?>
<?php foreach ( $byPrice as $price ): ?>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name"><?php echo $price->description ?></td>
    <?php $i=$o=$c=0; foreach ( $price->Tickets as $t ) if ( $t->cancelling ) { $o += $t->value; $c++; } else $i += $t->value; ?>
    <td class="nb"><?php echo $c; $total['nb-'] += $c ?></td>
    <td class="outcomes amount"><?php echo format_currency($o,'€'); $total['value-'] += $o ?></td>
    <td class="nb"><?php echo $price->Tickets->count()-$c; $c; $total['nb+'] += $price->Tickets->count()-$c; ?></td>
    <td class="incomes amount"><?php echo format_currency($i,'€'); $total['value+'] += $i; ?></td>
    <td class="total"><?php echo format_currency($i+$o,'€'); ?></td>
  </tr>
<?php endforeach ?>
</tbody>
<tfoot>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name">Total</td>
    <td class="nb"><?php echo $total['nb-'] ?></td>
    <td class="outcomes amount"><?php echo format_currency($total['value-'],'€') ?></td>
    <td class="nb"><?php echo $total['nb+'] ?></td>
    <td class="incomes amount"><?php echo format_currency($total['value+'],'€') ?></td>
    <td class="nb"><?php echo $total['nb+']-$total['nb-'] ?></td>
    <td class="total"><?php echo format_currency($total['value+']+$total['value-'],'€') ?></td>
  </tr>
</tfoot>
<thead>
  <tr>
    <td class="name"><?php echo __('Entitled') ?></td>
    <td class="nb"><?php echo __('Number') ?></td>
    <td class="outcomes"><?php echo __('Outcomes') ?></td>
    <td class="nb"><?php echo __('Number') ?></td>
    <td class="incomes"><?php echo __('Incomes') ?></td>
    <td class="nb"><?php echo __('Number') ?></td>
    <td class="total"><?php echo __('Total') ?></td>
  </tr>
</thead>
</table>

</div>
