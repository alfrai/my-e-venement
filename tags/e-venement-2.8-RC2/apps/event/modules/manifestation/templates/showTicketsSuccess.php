<?php use_helper('I18N', 'Date') ?>
<?php include_partial('manifestation/assets') ?>

<div id="sf_admin_container" class="sf_admin_show ui-widget ui-widget-content ui-corner-all">

  <div id="sf_fieldset_tickets">
    <?php if ( isset($cache) ): ?>
      <?php echo $sf_data->getRaw('cache') ?>
    <?php else: ?>
      <?php include_partial('show_tickets_list', array(
        'prices' => $prices,
        'configuration' => $configuration,
        'manifestation_id' => $manifestation_id,
      )) ?>
    <?php endif ?>
  </div>

</div>
