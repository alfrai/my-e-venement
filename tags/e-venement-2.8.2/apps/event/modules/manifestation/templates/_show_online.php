<div class="sf_admin_form_row sf_admin_form_field_online">
  <label><?php echo __('Online') ?>:</label>
  <?php
    echo $manifestation->online
      ? image_tag('/sfDoctrinePlugin/images/tick.png')
      : image_tag('/sfDoctrinePlugin/images/delete.png')
  ?>
</div>
