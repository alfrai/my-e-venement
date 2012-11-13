<?php use_helper('Number') ?>

<td><?php echo __('Prices') ?></td>
<td><?php echo $ticket->Price->name.' / '.$ticket->Price->description ?></td>
<td><?php echo format_currency($ticket->value,'€') ?></td>
<td></td>

