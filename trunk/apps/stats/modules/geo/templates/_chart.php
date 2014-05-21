<div class="ui-widget ui-corner-all ui-widget-content geo">
  <a name="chart"></a>
  <div class="chart">
    <?php echo liWidgetOfc::createChart(400, 250, $sf_context->getModuleName().'/data',true); ?>
    <div class="actions"><?php include_partial('global/chart_actions',array('anchor' => 'chart-printed')) ?></div>
  </div>
</div>
