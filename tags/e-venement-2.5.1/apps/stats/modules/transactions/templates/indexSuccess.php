<?php include_partial('attendance/filters',array('form' => $form)) ?>
<div class="ui-widget ui-corner-all ui-widget-content">
  <a name="chart-title"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <?php include_partial('attendance/filters_buttons') ?>
    <h1><?php echo __('Transactions by price',null,'menu') ?></h1>
  </div>
<?php include_partial('chart_printed') ?>
<?php include_partial('chart_ordered') ?>
<div class="clear"></div>
<?php if ( sfConfig::get('project_count_demands',false) ): ?>
<?php include_partial('chart_asked') ?>
<?php endif ?>
<?php include_partial('chart_all') ?>
<div class="clear"></div>
</div>
