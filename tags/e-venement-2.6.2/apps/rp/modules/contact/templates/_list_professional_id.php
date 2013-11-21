<?php foreach ( $contact->Professionals as $pro ): ?>
<div class="pro pro-<?php echo $pro->id ?>">
  <input type="checkbox" class="sf_admin_batch_checkbox" value="<?php echo $pro->id ?>" name="professional_ids[]" />
</div>
<?php endforeach ?>

