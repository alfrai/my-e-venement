<?php foreach ( $contact->Professionals as $pro ): ?>
<div class="pro pro-<?php echo $pro->id ?>">
  <?php echo $pro->Organism->city ?>
</div>
<?php endforeach ?>
