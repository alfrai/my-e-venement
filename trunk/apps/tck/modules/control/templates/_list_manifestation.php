<?php if ( intval($control->ticket_id).'' === ''.$control->ticket_id ): ?>
<?php echo cross_app_link_to($control->Ticket->Manifestation, 'event', 'manifestation/show?id='.$control->Ticket->manifestation_id) ?>
<?php endif ?>
