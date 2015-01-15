<?php if ( intval($control->ticket_id).'' === ''.$control->ticket_id ): ?>
  #<?php echo link_to($control->Ticket->id, 'ticket/show?id='.$control->Ticket->id) ?>
<?php else: ?>
  <span class="error" title="<?php echo $control->ticket_id ?>"><?php echo $control->ticket_id ?></span>
<?php endif ?>
