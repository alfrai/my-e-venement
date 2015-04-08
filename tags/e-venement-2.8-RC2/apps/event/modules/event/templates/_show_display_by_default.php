<div class="sf_admin_form_row">
  <label><?php echo __('Display in ticketting') ?>:</label>
  <?php
    echo $event->display_by_default
      ? image_tag('/sfDoctrinePlugin/images/tick.png')
      : image_tag('/sfDoctrinePlugin/images/delete.png')
  ?>
</div>
