<div class="sf_admin_form_row">
  <label><?php echo __('Gauge') ?>:</label>
  <?php if ( !isset($manifestation) ) $manifestation = $form->getObject(); ?>
  <?php include_partial('list_gauge',array('manifestation' => $manifestation)) ?>
</div>
