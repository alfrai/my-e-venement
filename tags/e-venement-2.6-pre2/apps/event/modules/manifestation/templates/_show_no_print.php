<div class="sf_admin_form_row sf_admin_form_field_no_print">
  <label><?php echo __('Preprinted ticketting') ?>:</label>
  <?php
    echo $manifestation->no_print
      ? image_tag('/sfDoctrinePlugin/images/tick.png')
      : image_tag('/sfDoctrinePlugin/images/delete.png')
  ?>
</div>
