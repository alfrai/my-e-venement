<div class="ui-widget ui-corner-all ui-widget-content charts-4">
  <a name="chart-tor"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h2><?php echo __('Types of resources') ?></h2>
  </div>
  <div class="chart">
    <?php echo liWidgetOfc::createChart(400, 250, $sf_context->getModuleName().'/data?id=tor',true); ?>
    <div class="actions"><?php include_partial('global/chart_actions',array('anchor' => 'chart-tor', 'id' => 'tor')) ?></div>
  </div>
</div>
