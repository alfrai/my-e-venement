<div class="ui-widget-content ui-corner-all" id="byUser">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h2><?php echo __("Users' summary") ?></h2>
  </div>

<table>
<tbody>
<?php $total = array('nb' => 0, 'nb_free' => 0, 'nb_paying' => 0, 'nb_cancelling' => 0, 'value+' => 0, 'value-' => 0); $class = false; ?>
<?php foreach ( $byUser as $u ): ?>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name"><?php echo $u ?></td>
    <td class="nb"><?php echo $u->nb_cancelling; $total['nb_cancelling'] += $u->nb_cancelling ?></td>
    <td class="outcome amount"><?php echo format_currency($u->outcome,'€'); $total['value-'] += $u->outcome ?></td>
    <td class="nb"><?php echo $u->nb_paying; $total['nb_paying'] += $u->nb_paying ?></td>
    <td class="income amount"><?php echo format_currency($u->income,'€'); $total['value+'] += $u->income ?></td>
    <td class="average" title="<?php echo __('Without cancellations') ?>"><?php echo $u->nb_paying > 0 ? format_currency($u->income/$u->nb_paying,'€') : 'N/A' ?></td>
    <td class="nb"><?php echo $u->nb_free; $total['nb_free'] += $u->nb_free ?></td>
    <td class="average" title="<?php echo __('Without cancellations') ?>"><?php echo $u->nb_free+$u->nb_paying > 0 ? format_currency($u->income/($u->nb_free+$u->nb_paying),'€') : 'N/A' ?></td>
    <td class="nb"><?php echo $u->nb_paying + $u->nb_free + $u->nb_cancelling ?></td>
    <td class="total"><?php echo format_currency($u->income+$u->outcome,'€') ?></td>
  </tr>
<?php endforeach ?>
<tbody>
<tfoot>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name">Total</td>
    <td class="nb"><?php echo $total['nb_cancelling'] ?></td>
    <td class="outcome amount"><?php echo format_currency($total['value-'],'€') ?></td>
    <td class="nb"><?php echo $total['nb_paying'] ?></td>
    <td class="income amount"><?php echo format_currency($total['value+'],'€') ?></td>
    <td class="average" title="<?php echo __('Without cancellations') ?>"><?php echo $total['nb_paying'] > 0 ? format_currency(($total['value+']/$total['nb_paying']),'€') : 'N/A' ?></td>
    <td class="nb"><?php echo $total['nb_free'] ?></td>
    <td class="average" title="<?php echo __('Without cancellations') ?>"><?php echo $total['nb_free']+$total['nb_paying'] > 0 ? format_currency($total['value+']/($total['nb_free']+$total['nb_paying']),'€') : 'N/A' ?></td>
    <td class="nb"><?php echo $total['nb_free'] + $total['nb_paying'] + $total['nb_cancelling'] ?></td>
    <td class="total"><?php echo format_currency($total['value+']+$total['value-'],'€') ?></td>
  </tr>
</tfoot>
<thead>
  <tr>
    <td class="name"><?php echo __('User') ?></td>
    <td class="nb"><?php echo __('Cancellations') ?></td>
    <td class="outcomes amount"><?php echo __('Outcomes') ?></td>
    <td class="nb"><?php echo __('Paid tickets') ?></td>
    <td class="incomes amount"><?php echo __('Incomes') ?></td>
    <td class="average"><?php echo __('Paid tickets average') ?></td>
    <td class="nb"><?php echo __('Free tickets') ?></td>
    <td class="average"><?php echo __('Average') ?></td>
    <td class="nb"><?php echo __('Number') ?></td>
    <td class="total"><?php echo __('Total') ?></td>
  </tr>
</thead>
</table>

</div>
