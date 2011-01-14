<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_test_button">
<?php if ( !$form->isNew() ): ?>
<script type="text/javascript">
$(document).ready(function(){
  if ( $('.ui-state-error-text:first').length > 0 )
    $('.ui-state-error-text:first').click();
  else
    $('a[href="#sf_fieldset_4__send"]:first').click();
});
</script>
<?php endif; ?>
<button type="submit" id="email-test-button" class="fg-button ui-state-default fg-button-icon-left">
  <span class="ui-icon ui-icon-circle-check"></span>
  <?php echo __('Test email') ?>
</button>
</div>
