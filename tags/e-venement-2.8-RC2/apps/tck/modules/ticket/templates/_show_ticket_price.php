<?php use_helper('Number') ?>

<td><?php echo __('Prices') ?></td>
<td><?php echo $ticket->Price->name.' / '.$ticket->Price->description ?></td>
<td>
  <span class="value"><?php echo format_currency($ticket->value,'€') ?></span>
  <?php if ( $ticket->taxes ): ?>
    +
    <?php echo __('Extra taxes') ?>
    <span class="extra-taxes"><?php echo format_currency($ticket->taxes,'€') ?></span>
    =
    <span class="total"><?php echo format_currency($ticket->taxes+$ticket->value,'€') ?></span>
  <?php endif ?>
</td>
<td class="vat" title="<?php echo __('VAT') ?>"><?php echo $ticket->vat * 100 ?>%</td>

