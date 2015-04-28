<div class="ui-widget ui-corner-all ui-widget-content geo charts-4">
  <a name="chart"></a>
  <div class="chart-<?php echo $type ?>">
    <div class="ui-widget-header ui-corner-all fg-toolbar">
      <h2><?php echo $title ?></h2>
    </div>
    <?php echo liWidgetOfc::createChart(400, 250, $sf_context->getModuleName().'/data?type='.$type,true); ?>
    <div class="actions"><?php include_partial('global/chart_actions',array('anchor' => 'chart-printed', 'get_param' => 'type', 'id' => $type)) ?></div>
  </div>
</div>
