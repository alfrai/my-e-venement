<?php if ( intval($control->ticket_id).'' === ''.$control->ticket_id ): ?>
#<?php echo link_to($control->Ticket->transaction_id, 'transaction/edit?id='.$control->Ticket->transaction_id) ?>
<?php endif ?>
