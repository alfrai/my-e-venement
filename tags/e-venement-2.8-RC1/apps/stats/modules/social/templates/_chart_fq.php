<div class="ui-widget ui-corner-all ui-widget-content charts-4">
  <a name="chart-fq"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h2><?php echo __('Familial quotients') ?></h2>
  </div>
  <div class="chart">
    <?php echo liWidgetOfc::createChart(400, 250, $sf_context->getModuleName().'/data?id=fq',true); ?>
    <div class="actions"><?php include_partial('global/chart_actions',array('anchor' => 'chart-fq', 'id' => 'fq')) ?></div>
  </div>
</div>
