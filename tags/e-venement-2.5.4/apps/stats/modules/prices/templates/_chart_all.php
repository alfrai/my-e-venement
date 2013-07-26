<div class="ui-widget ui-corner-all ui-widget-content charts-4">
  <a name="chart-all"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h2><?php echo __('Global repartition') ?></h2>
  </div>
  <div class="chart">
    <?php echo liWidgetOfc::createChart(400, 250, $sf_context->getModuleName().'/data?id=all',true); ?>
    <div class="actions"><?php include_partial('global/chart_actions',array('anchor' => 'chart-all', 'id' => 'all')) ?></div>
  </div>
</div>
