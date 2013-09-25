<div class="sf_admin_form_row sf_admin_form_field_<?php echo $fieldName ?> sf_admin_boolean">
  <label><?php echo $label ?>:</label>
  <?php echo image_tag($form->getObject()->$fieldName ? '/sfDoctrinePlugin/images/tick.png' : '/sfDoctrinePlugin/images/delete.png') ?>
</div>
