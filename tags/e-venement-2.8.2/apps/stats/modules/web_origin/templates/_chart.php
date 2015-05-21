<div class="ui-widget ui-corner-all ui-widget-content charts-4">
  <a name="chart-all"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h2><?php echo $title ?></h2>
  </div>
  <div class="chart">
    <?php echo liWidgetOfc::createChart(550, 370, $sf_context->getModuleName().'/data?which='.$target,true); ?>
    <div class="actions"><?php include_partial('global/chart_actions',array('anchor' => 'chart-'.$target, 'id' => $target, 'get_param' => 'which')) ?></div>
  </div>
</div>

