<td><?php echo __('Cancellations') ?></td>
<td><?php foreach ( $ticket->Cancelling as $t ) echo link_to('#'.$t->id,'ticket/show?id='.$t->id).' '; ?></td>
<td><?php foreach ( $ticket->Cancelled  as $t ) echo link_to('#'.$t->id,'ticket/show?id='.$t->id).' '; ?></td>
<td></td>
