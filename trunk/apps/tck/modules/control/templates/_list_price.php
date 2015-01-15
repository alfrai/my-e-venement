<?php if ( intval($control->ticket_id).'' === ''.$control->ticket_id ): ?>
<?php echo $control->Ticket->price_id ? $control->Ticket->Price : $control->Ticket->price_name ?>
<?php endif ?>
