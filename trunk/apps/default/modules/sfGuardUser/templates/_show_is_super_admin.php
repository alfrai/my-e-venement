<div class="sf_admin_form_row">
  <label><?php echo __('Is super admin') ?>:</label>
  <?php echo get_partial('sfGuardUser/list_field_boolean', array('value' => $form->getObject()->getIsSuperAdmin())) ?>
</div>
