<?php $email = htmlspecialchars($form->getObject()->$field) ?>
<div class="sf_admin_form_row">
  <label><?php echo __($label) ?>:</label>
  <a href="mailto:<?php echo $email ?>"><?php echo $email ?></a>
</div>
