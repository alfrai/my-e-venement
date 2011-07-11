<div class="ui-widget ui-corner-all ui-widget-content">
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h1><?php echo __('Gauge filling') ?></h1>
  </div>
  <div class="chart">
    <?php echo liWidgetOfc::createChart(900,530,'attendance/data'); ?>
  </div>
  <div class="actions"><?php include_partial('global/chart_actions') ?></div>
</div>
