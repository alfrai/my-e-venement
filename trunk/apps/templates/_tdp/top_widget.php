<div id="tdp-top-bar" class="ui-widget-content ui-corner-all">

<div class="tdp-top-modules tdp-top-widget skew">
<?php include_partial('global/tdp/top_widget_modules',array(
  'object' => $object,
)) ?>
</div>
<div class="tdp-top-widget vertical">
<?php include_partial('global/tdp/top_widget_actions',array(
  'object' => $object,
  'config' => $config,
)) ?>
</div>
<div class="tdp-top-widget vertical">
<?php include_partial('global/tdp/top_widget_misc',array(
  'object' => $object,
  'hasFilters' => $hasFilters,
)) ?>
</div>
<div class="tdp-top-widget vertical">
<?php include_partial('global/tdp/top_widget_filters_buttons',array(
  'object' => $object,
  'hasFilters' => $hasFilters,
  'filters' => $filters,
  'configuration' => $configuration,
)) ?>
</div>
<div id="sf_admin_bar ui-helper-hidden" style="display:none">
  <?php if ( !$object ) include_partial($sf_context->getModuleName().'/filters', array('form' => $filters, 'configuration' => $configuration)) ?>
</div>

</div>
