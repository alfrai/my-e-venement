<?php include_partial('attendance/filters',array('form' => $form)) ?>
<?php use_helper('Date') ?>
<div class="ui-widget ui-corner-all ui-widget-content">
  <a name="chart-title"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <?php include_partial('attendance/filters_buttons') ?>
    <h1><?php echo __('Evolution of groups',null,'menu') ?></h1>
  </div>
  <?php //include_partial('show_criterias') ?>
  <div class="chart">
    <?php echo liWidgetOfc::createChart(900,700,'groups/data',true); ?>
  </div>
  <div class="actions"><?php include_partial('global/chart_actions') ?></div>
</div>
