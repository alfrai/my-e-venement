<div class="ui-widget-content ui-corner-all" id="byPaymentMethod">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h2><?php echo __("Payment modes") ?></h2>
  </div>
  
<table>
<tbody>
<?php $total = array('nb' => 0, 'value+' => 0, 'value-' => 0) ?>
<?php foreach ( $byPaymentMethod as $pm ): ?>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="name"><?php echo $pm ?></td>
    <td class="nb"><?php echo $pm->Payments->count(); $total['nb'] += $pm->Payments->count() ?></td>
    <?php $i=$o=0; foreach ( $pm->Payments as $p ) if ( $p->value > 0 ) $i += $p->value; else $o += $p->value; ?>
    <?php
      $proportion = 1;
      $sum = $part = 0;
      //if ( false )
      if ( is_array($form->getValue('manifestations')) && count($form->getValue('manifestations')) > 0 )
      {
        foreach ( $pm->Payments as $payment )
        foreach ( $payment->Transaction->Tickets as $tck )
        {
          $part += $tck->value;
          foreach ( $tck->Transaction->Tickets as $tck2 )
            $sum += $tck2->value;
        }
        $proportion = $sum == 0 ? 1 : $part / $sum;
      }
    ?>
    <td class="outcomes amount"><?php echo format_currency($o * $proportion,'€'); $total['value-'] += ($o * $proportion) ?></td>
    <td class="incomes amount"><?php echo format_currency($i * $proportion,'€'); $total['value+'] += ($i * $proportion) ?></td>
    <td class="total"><?php echo format_currency(($i+$o) * $proportion,'€'); ?></td>
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
