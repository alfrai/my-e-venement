<?php foreach ( $contact->Professionals as $pro ): ?>
<div class="pro pro-<?php echo $pro->id ?>">
  <?php echo link_to($pro->Organism,'organism/show?id='.$pro->Organism->id) ?>
</div>
<?php endforeach ?>
