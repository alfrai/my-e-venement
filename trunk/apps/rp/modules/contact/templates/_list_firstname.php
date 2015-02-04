<?php if ( $contact->firstname ): ?>
<?php echo link_to($contact->firstname, 'contact/edit?id='.$contact->id) ?>
<?php endif ?>
