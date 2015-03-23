<?php use_helper('Date') ?>
<div class="sf_admin_form_row sf_admin_form_field_<?php echo $fieldName ?> sf_admin_date">
  <label><?php echo $label ?>:</label>
  <?php if ( $v = $form->getObject()->current_version ): ?>
  <span class="diff">
    <?php
      if ( !isset($v->$fieldName) )
        echo 'n/a';
      else
        echo format_datetime($v->$fieldName,'EEE dd MMM yyyy HH:mm');
    ?>
  </span>
  <?php endif ?>
  <?php echo format_datetime($form->getObject()->$fieldName,'EEE dd MMM yyyy HH:mm') ?>
</div>

