<div class="sf_admin_form_row sf_admin_form_field_contact_id">
  <label><?php echo __('Applicant') ?>:</label>
  <?php if ( $v = $manifestation->current_version ): ?>
  <span class="diff">
    <?php if ( $v->contact_id && $c = Doctrine::getTable('Contact')->findOneById($v->contact_id) ): ?>
      <?php echo cross_app_link_to($c, 'rp', 'contact/show?id='.$v->contact_id) ?>
    <?php endif ?>
  </span>
  <?php endif ?>
  <?php echo cross_app_link_to($form->getObject()->Applicant, 'rp', 'contact/show?id='.$form->getObject()->contact_id) ?>
</div>
