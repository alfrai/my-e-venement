<div class="sf_admin_form_row depends_on sf_admin_form_field_depends_on">
  <label><?php echo __('Depends on') ?>:</label>
  <?php if ( $v = $manifestation->current_version ): ?>
  <span class="diff">
    <?php if ( $v->depends_on && $do = Doctrine::getTable('Manifestation')->findOneById($v->depends_on) ): ?>
      <?php echo link_to($do, 'manifestation/show?id='.$v->depends_on) ?>
    <?php endif ?>
  </span>
  <?php endif ?>
  <?php if ( $manifestation->depends_on ) echo link_to($manifestation->DependsOn, 'manifestation/show?id='.$manifestation->depends_on) ?>
</div>
