<?php if ( intval($control->ticket_id).'' === ''.$control->ticket_id ): ?>
<?php echo link_to($control->Ticket->Manifestation, 'manifestation/show?id='.$control->Ticket->Manifestation->id) ?>
<?php endif ?>
