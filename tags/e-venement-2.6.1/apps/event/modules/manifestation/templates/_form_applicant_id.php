<div class="sf_admin_form_row sf_admin_form_field_applicant">
  <?php if ( !$form->getObject()->Applicant->isNew() ): ?>
  <div class="label ui-helper-clearfix"><label><?php echo __('Applicant') ?>:</label></div>
  <?php echo cross_app_link_to($form->getObject()->Applicant, 'rp', 'contact/show?id='.$form->getObject()->Applicant->id) ?>
  <?php endif ?>
</div>
