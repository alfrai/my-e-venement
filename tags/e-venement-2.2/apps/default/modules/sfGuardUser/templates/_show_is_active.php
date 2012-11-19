<div class="sf_admin_form_row">
  <label><?php echo __('Is active') ?>:</label>
  <?php echo get_partial('sfGuardUser/list_field_boolean', array('value' => $form->getObject()->getIsActive())) ?>
</div>
