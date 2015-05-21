<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all gmap">
  <div class="ui-widget-header ui-corner-all fg-toolbar"><h2><?php echo __('Geolocalization') ?></h2></div>
  <div class="ui-widget-gmap">
    <?php include_partial('global/gmap_one_only',array('form' => $form, 'width' => isset($width) ? $width : '280px', 'height' => '350px')); ?>
  </div>
</div>
