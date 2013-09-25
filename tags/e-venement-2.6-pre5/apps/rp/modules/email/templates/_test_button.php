<div class="sf_admin_form_row sf_admin_button sf_admin_form_field_test_button">
<?php if ( $form->isNew() ): ?>
<span class="sf_admin_form_is_new"></span>
<?php endif ?>
<button type="submit" name="email-test-button" value="test" id="email-test-button" class="fg-button ui-state-default fg-button-icon-left">
  <span class="ui-icon ui-icon-circle-check"></span>
  <?php echo __('Test email') ?>
</button>
</div>
