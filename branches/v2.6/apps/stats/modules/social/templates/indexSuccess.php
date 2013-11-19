<?php include_partial('attendance/filters',array('form' => $form)) ?>
<div class="ui-widget ui-corner-all ui-widget-content">
  <a name="chart-title"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <?php include_partial('attendance/filters_buttons') ?>
    <h1><?php echo __('Social statistics',null,'menu') ?></h1>
  </div>
<?php include_partial('chart_fs') ?>
<?php include_partial('chart_fq') ?>
<?php include_partial('chart_tor') ?>
<div class="clear"></div>
</div>
