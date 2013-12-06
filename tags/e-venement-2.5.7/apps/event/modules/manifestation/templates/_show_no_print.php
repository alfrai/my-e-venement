<div class="sf_admin_form_row">
  <label><?php echo __('Preprinted ticketting') ?>:</label>
  <?php
    echo $manifestation->no_print
      ? image_tag('/sfDoctrinePlugin/images/tick.png')
      : image_tag('/sfDoctrinePlugin/images/delete.png')
  ?>
</div>
