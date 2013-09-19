<?php foreach ( $contact->Professionals as $pro ): ?>
<div class="pro pro-<?php echo $pro->id ?>">
  <?php echo $pro->description ? $pro->description : $contact->description ?>
</div>
<?php endforeach ?>

