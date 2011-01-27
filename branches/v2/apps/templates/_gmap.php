<?php use_helper('I18N', 'Date') ?>
<?php use_helper('GMap') ?>
<?php include_partial('contact/assets') ?>
<div class="ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix">
  <div id="gmap" class="ui-widget-content ui-corner-all">
    <?php include_map($gMap,array('width' => '750px')); ?>
    <?php include_map_javascript($gMap); ?>
  </div>
</div>

