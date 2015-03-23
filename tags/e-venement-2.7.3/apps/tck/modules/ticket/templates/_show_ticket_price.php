<?php use_helper('Number') ?>

<td><?php echo __('Prices') ?></td>
<td><?php echo $ticket->Price->name.' / '.$ticket->Price->description ?></td>
<td><?php echo format_currency($ticket->value,'€') ?></td>
<td class="vat" title="<?php echo __('VAT') ?>"><?php echo $ticket->vat * 100 ?>%</td>

