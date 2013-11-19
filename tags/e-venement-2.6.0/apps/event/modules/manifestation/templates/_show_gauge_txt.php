<div class="sf_admin_form_row sf_admin_form_field_gauge_txt">
  <label><?php echo __('Gauge') ?>:</label>
  <?php if ( !isset($manifestation) ) $manifestation = $form->getObject(); ?>
  <?php include_partial('list_gauge',array('manifestation' => $manifestation)) ?>
</div>
