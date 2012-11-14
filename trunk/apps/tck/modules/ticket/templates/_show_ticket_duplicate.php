<td><?php echo __('Duplicatas') ?></td>
<td title="<?php echo __('Original') ?>"><?php foreach ( $ticket->Duplicated as $t ) echo link_to('#'.$t->id,'ticket/show?id='.$t->id).' '; ?></td>
<td title="<?php echo __('Duplicata') ?>"><?php foreach ( $ticket->Duplicata  as $t ) echo link_to('#'.$t->id,'ticket/show?id='.$t->id).' '; ?></td>
<td></td>

