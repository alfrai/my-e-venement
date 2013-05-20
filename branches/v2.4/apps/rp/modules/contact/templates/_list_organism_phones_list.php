<?php foreach ( $contact->Professionals as $pro ): ?>
<div class="pro pro-<?php echo $pro->id ?>">
  <?php foreach ( $pro->Organism->Phonenumbers as $phone ): ?>
  <span title="<?php echo $phone->name ?>"><?php echo $phone->number ?></span>,
  <?php endforeach ?>
</div>
<?php endforeach ?>
