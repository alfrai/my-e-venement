<?php
  $name = 'is_super_admin';
  $label = 'Is super admin ?';
  $help = '';
  $class = 'sf_admin_form_row sf_admin_boolean sf_admin_form_field_'.$name;
?>
  <div class="<?php echo $class ?><?php $form[$name]->hasError() and print ' ui-state-error ui-corner-all' ?>">
    <div class="label ui-helper-clearfix">
      <?php echo $form[$name]->renderLabel($label) ?>

      <?php if ($help || $help = $form[$name]->renderHelp()): ?>
        <div class="help">
          <span class="ui-icon ui-icon-help floatleft"></span>
          <?php echo __(strip_tags($help), array(), 'messages') ?>
        </div>
      <?php endif; ?>
    </div>

    <?php if ( $sf_user->isSuperAdmin() ): ?>
    <?php echo $form[$name]->render($attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes) ?>
    <?php else: ?>
    <?php echo image_tag($form->getObject()->is_super_admin ? '/sfDoctrinePlugin/images/tick.png' : '/sfDoctrinePlugin/images/delete.png') ?>
    <?php endif ?>

    <?php if ($form[$name]->hasError()): ?>
      <div class="errors">
        <span class="ui-icon ui-icon-alert floatleft"></span>
        <?php echo $form[$name]->renderError() ?>
      </div>
    <?php endif; ?>
  </div>
