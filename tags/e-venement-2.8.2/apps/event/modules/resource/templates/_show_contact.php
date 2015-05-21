<?php
  $model  = 'Contact';
  $title  = 'Stage manager';
  $module =
  $class  = strtolower($model);
  $app    = 'rp';
?>
<div class="sf_admin_form_row sf_admin_field_<?php echo $class ?>">
  <label><?php echo __($title) ?>:</label>
  <?php if ( $form->getObject()->$model ): ?>
  <a href="<?php echo cross_app_url_for($app,$module.'/edit?id='.$form->getObject()->$model->id) ?>">
    <?php echo $form->getObject()->$model ?>
  </a>
  <?php endif ?>
</div>
