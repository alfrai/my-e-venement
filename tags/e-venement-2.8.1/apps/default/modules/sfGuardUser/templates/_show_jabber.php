<?php if ( $form->getObject()->Jabber->count() > 0 ): ?>
<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_jabber">
  <div class="label"><label><?php echo __('Jabber') ?>:</label></div>
  <span><?php echo $form->getObject()->Jabber[0] ?></span>
</div>
<?php endif ?>
