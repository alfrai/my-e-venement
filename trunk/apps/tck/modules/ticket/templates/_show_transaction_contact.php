<?php use_helper('CrossAppLink') ?>

<td><?php echo __('Transaction') ?></td>
<td>#<?php echo link_to($ticket->Transaction,'ticket/sell?id='.$ticket->Transaction->id) ?></td>
<td><a href="<?php echo cross_app_url_for('rp','contact/show?id='.$ticket->Transaction->contact_id) ?>"><?php echo $ticket->Transaction->Contact ?></a></td>
<td><?php echo $ticket->Transaction->Professional ?></td>

