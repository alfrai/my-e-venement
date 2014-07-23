<div class="sf_admin_form_row sf_admin_form_field_<?php echo $fieldName ?> sf_admin_boolean">
  <label><?php echo $label ?>:</label>
  <?php if ( $v = $form->getObject()->current_version ): ?>
  <span class="diff">
    <?php if ( isset($v->$fieldName) ): ?>
      <?php echo image_tag($v->$fieldName ? '/sfDoctrinePlugin/images/tick.png' : '/sfDoctrinePlugin/images/delete.png') ?>
    <?php else: ?>
      n/a
    <?php endif ?>
  </span>
  <?php endif ?>
  <?php echo image_tag($form->getObject()->$fieldName ? '/sfDoctrinePlugin/images/tick.png' : '/sfDoctrinePlugin/images/delete.png') ?>
</div>
