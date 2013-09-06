<?php use_helper('Date') ?>
<td><?php echo __('User') ?></td>
<td><?php echo $ticket->User ?></td>
<td><?php echo format_datetime($ticket->printed_at ? $ticket->printed_at : ($ticket->integrated_at ? $ticket->integrated_at : $ticket->updated_at)) ?></td>
<td></td>

