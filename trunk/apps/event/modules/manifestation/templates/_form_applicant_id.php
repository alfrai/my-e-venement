<div class="sf_admin_form_row sf_admin_form_field_applicant">
  <?php if ( $form->getObject()->Applicant ): ?>
  <div class="label ui-helper-clearfix"><label><?php echo __('The applicant') ?>:</label></div>
  <?php echo cross_app_link_to($form->getObject()->Applicant, 'rp', 'contact/show?id='.$form->getObject()->Applicant->id) ?>
  <?php endif ?>
</div>
