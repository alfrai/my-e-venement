<div class="sf_admin_form_row">
  <label><?php echo __('Seated') ?>:</label>
  <?php
    echo $manifestation->seated
      ? image_tag('/sfDoctrinePlugin/images/tick.png')
      : image_tag('/sfDoctrinePlugin/images/delete.png')
  ?>
</div>
