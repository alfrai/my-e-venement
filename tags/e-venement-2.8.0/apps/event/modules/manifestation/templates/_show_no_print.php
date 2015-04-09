<div class="sf_admin_form_row sf_admin_form_field_no_print">
  <label><?php echo __('Preprinted ticketting') ?>:</label>
  <?php if ( $v = $manifestation->current_version ): ?>
  <span class="diff">
    <?php
      echo $v->no_print
        ? image_tag('/sfDoctrinePlugin/images/tick.png')
        : image_tag('/sfDoctrinePlugin/images/delete.png')
    ?>
  </span>
  <?php endif ?>
  <?php
    echo $manifestation->no_print
      ? image_tag('/sfDoctrinePlugin/images/tick.png')
      : image_tag('/sfDoctrinePlugin/images/delete.png')
  ?>
</div>
