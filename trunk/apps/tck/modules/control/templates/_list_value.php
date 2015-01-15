<?php if ( intval($control->ticket_id).'' === ''.$control->ticket_id ): ?>
<?php use_helper('Number') ?>
<?php echo format_currency($control->Ticket->value, '€') ?>
<?php endif ?>
