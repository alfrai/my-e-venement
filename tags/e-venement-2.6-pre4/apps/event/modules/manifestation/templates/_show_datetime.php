<?php use_helper('Date') ?>
<div class="sf_admin_form_row sf_admin_form_field_<?php echo $fieldName ?> sf_admin_date">
  <label><?php echo $label ?>:</label>
  <?php echo format_datetime($form->getObject()->$fieldName,'EEE dd MMM yyyy HH:mm') ?>
</div>

