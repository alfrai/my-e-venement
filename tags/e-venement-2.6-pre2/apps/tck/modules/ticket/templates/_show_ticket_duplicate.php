<td><?php echo __('Duplicatas') ?></td>
<td title="<?php echo __('Original') ?>"><?php if ( $ticket->duplicating ) echo link_to('#'.$ticket->duplicating,'ticket/show?id='.$ticket->duplicating).' '; ?></td>
<td title="<?php echo __('Duplicata') ?>"><?php foreach ( $ticket->Duplicatas as $t ) echo link_to('#'.$t->id,'ticket/show?id='.$t->id).' '; ?></td>
<td></td>

