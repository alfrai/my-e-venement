<?php $email = $form->getObject()->get('email') ?>
<div class="sf_admin_form_row">
  <label><?php echo __('Email') ?>:</label>
  <a href="mailto:<?php echo $email ?>"><?php echo $email ?></a>
</div>
