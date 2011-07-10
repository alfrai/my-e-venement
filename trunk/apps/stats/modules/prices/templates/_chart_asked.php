<div class="ui-widget ui-corner-all ui-widget-content charts-4">
  <a name="chart-asked"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h1><?php echo __('Asked tickets') ?></h1>
  </div>
  <div class="chart">
    <?php stOfc::createChart(400, 250, $sf_context->getModuleName().'/data?id=asked', false); ?>
    <div class="actions"><?php include_partial('global/chart_actions',array('anchor' => 'chart-asked', 'id' => 'asked')) ?></div>
  </div>
</div>
