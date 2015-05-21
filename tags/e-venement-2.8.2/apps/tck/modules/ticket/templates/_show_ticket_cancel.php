<td><?php echo __('Cancellations') ?></td>
<td title="<?php echo __('Cancellations') ?>"><?php foreach ( $ticket->Cancelling as $t ) echo '#'.link_to($t->id,'ticket/show?id='.$t->id).' '; ?></td>
<td title="<?php echo __('Cancelling') ?>"   ><?php $t = $ticket->Cancelled; if ( $t ) echo '#'.link_to($t->id,'ticket/show?id='.$t->id).' '; ?></td>
<td></td>
