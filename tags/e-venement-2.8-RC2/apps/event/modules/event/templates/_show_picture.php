<?php use_stylesheet('picture','first') ?>
<div class="sf_admin_form_row <?php if ( !isset($seated_plan) ): ?>sf_admin_boolean sf_admin_form_field_show_picture<?php endif ?>">
  <label for="seated_plan_show_picture"><?php echo __('Poster') ?></label>
  <div class="widget">
  <?php if ( $form->getObject()->picture_id ): ?>
    <?php echo $form->getObject()->Picture->render() ?>
  <?php endif ?>
  </div>
</div>
