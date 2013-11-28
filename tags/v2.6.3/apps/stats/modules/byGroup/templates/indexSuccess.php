<?php //include_partial('attendance/filters',array('form' => $form)) ?>
<div class="ui-widget ui-corner-all ui-widget-content">
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <?php //include_partial('attendance/filters_buttons') ?>
    <h1><?php echo __('Entrances by group') ?></h1>
  </div>
  <div class="chart">
    <p>Pour les 3 dernières manifestations et la prochaine...</p>
    <?php echo liWidgetOfc::createChart(900, 530, 'byGroup/data', true); ?>
  </div>
  <div class="actions"><?php include_partial('global/chart_actions') ?></div>
</div>
