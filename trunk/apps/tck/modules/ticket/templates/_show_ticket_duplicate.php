<td><?php echo __('Duplicatas') ?></td>
<td><?php foreach ( $ticket->Duplicated as $t ) echo link_to('#'.$t->id,'ticket/show?id='.$t->id).' '; ?></td>
<td><?php foreach ( $ticket->Duplicata  as $t ) echo link_to('#'.$t->id,'ticket/show?id='.$t->id).' '; ?></td>
<td></td>

