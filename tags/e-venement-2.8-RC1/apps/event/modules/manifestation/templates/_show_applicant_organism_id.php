<div class="sf_admin_form_row sf_admin_form_field_organism_id">
  <label><?php echo __('Applied by organism') ?>:</label>
  <?php if ( $v = $manifestation->current_version ): ?>
  <span class="diff">
    <?php if ( $v->organism_id && $o = Doctrine::getTable('Organism')->findOneById($v->organism_id) ): ?>
      <?php echo cross_app_link_to($o, 'rp', 'organism/show?id='.$v->organism_id) ?>
    <?php endif ?>
  </span>
  <?php endif ?>
  <?php echo cross_app_link_to($form->getObject()->ApplicantOrganism, 'rp', 'organism/show?id='.$form->getObject()->organism_id) ?>
</div>
