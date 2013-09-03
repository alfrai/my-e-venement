<div class="sf_admin_form_row sf_admin_form_field_contact_id">
  <div class="label ui-helper-clearfix"><label><?php echo __('The applicant') ?>:</label></div>
  <?php echo cross_app_link_to($form->getObject()->Applicant, 'rp', 'contact/show?id='.$form->getObject()->id) ?>
</div>
