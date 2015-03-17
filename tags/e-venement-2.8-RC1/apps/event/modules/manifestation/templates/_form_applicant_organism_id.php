<div class="sf_admin_form_row sf_admin_form_field_applicant_organism">
  <?php if ( !$form->getObject()->Applicant->isNew() ): ?>
  <div class="label ui-helper-clearfix"><label><?php echo __('Applied by organism') ?>:</label></div>
  <?php echo cross_app_link_to($form->getObject()->ApplicantOrganism, 'rp', 'organism/show?id='.$form->getObject()->ApplicantOrganism->id) ?>
  <?php endif ?>
</div>
