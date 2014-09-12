<?php use_javascript('helper') ?>
<?php use_javascript('manifestation-price-gauges') ?>
<?php use_javascript('jquery.nicescroll.min.js') ?>
<?php $manifestation = $form->getObject(); ?>
<div class="sf_admin_form_row sf_admin_table sf_admin_form_field_gauges_prices">
<label for="gauge_prices"><?php echo __('Prices list') ?></label>
<div class="ui-widget-content ui-corner-all">
  <?php include_partial('widget_gauges_prices', array(
    'manifestation' => $manifestation,
    'edit' => false,
  )) ?>
</div>
</div>
