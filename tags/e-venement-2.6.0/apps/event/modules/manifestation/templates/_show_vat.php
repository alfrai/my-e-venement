<div class="sf_admin_form_row sf_admin_form_field_vat">
  <label><?php echo __('Vat') ?>:</label>
  <?php if ( $v = $manifestation->current_version ): ?>
    <span class="diff">
      <?php $vat = Doctrine::getTable('Vat')->findOneById($v->vat_id) ?>
      <?php echo $vat->value*100 ?>%
    </span>
  <?php endif ?>
  <?php echo floatval($manifestation->Vat->value*100) ?>%
</div>
