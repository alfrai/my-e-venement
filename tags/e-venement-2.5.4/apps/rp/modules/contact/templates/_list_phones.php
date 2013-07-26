<?php foreach ( $contact->Phonenumbers as $phone ): ?>
  <span title="<?php echo $phone->name ?>"><?php echo $phone->number ?></span>,
<?php endforeach ?>
