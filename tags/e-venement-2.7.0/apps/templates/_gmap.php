<?php return ?>
<?php use_helper('I18N', 'Date') ?>
<?php use_helper('GMap') ?>
<?php include_partial('assets') ?>
<?php $width = isset($width) ? $width : '750px' ?>
<?php $height = isset($height) ? $width : '500px' ?>
<div class="ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix">
  <div id="gmap" style="width: <?php echo $width ?>" class="ui-widget-content ui-corner-all">
    <?php include_map($gMap,array('width' => $width, 'height' => $height)); ?>
    <?php include_map_javascript($gMap); ?>
  </div>
</div>
