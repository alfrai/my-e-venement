<div class="ui-widget ui-corner-all ui-widget-content charts-4">
<?php foreach ( $form->getObject()->Queries as $query ): ?>
<?php if ( $query->stats !== 'free' ): ?>
  <a name="chart-all"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h2><?php echo $query ?></h2>
  </div>
  <div class="chart">
    <?php echo liWidgetOfc::createChart(400, 250, 'query/data?id='.$query->id, true); ?>
    <div class="actions"><?php include_partial('global/chart_actions',array('module' => 'query', 'anchor' => 'chart-all', 'id' => $query->id)) ?></div>
  </div>
<?php endif ?>
<?php endforeach ?>
</div>
