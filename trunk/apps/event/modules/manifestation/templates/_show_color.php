<div class="sf_admin_form_row sf_admin_form_field_color">
  <label><?php echo __('Color') ?>:</label>
  <?php if ( $manifestation->current_version ): ?>
  <span class="diff">
    <?php if ( $manifestation->current_version->color_id ): ?>
    <?php $color = Doctrine::getTable('Color')->findOneById($manifestation->current_version->color_id) ?>
    <span style="background-color: #<?php echo $color->color ?>; padding: 2px 30px;">
      <?php echo $color ?>
    </span>
    <?php else: ?>
      <?php echo 'n/a' ?>
    <?php endif ?>
  </span>
  <?php endif ?>
  <span style="background-color: #<?php echo $manifestation->Color->color ?>; padding: 2px 30px;">
    <?php echo $manifestation->Color->name ?>
  </span>
</div>
