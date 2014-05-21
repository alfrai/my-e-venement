<?php include_partial('attendance/filters',array('form' => $form)) ?>
<?php use_helper('Date') ?>
<div class="ui-widget ui-corner-all ui-widget-content">
  <a name="chart-title"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <?php include_partial('attendance/filters_buttons') ?>
    <h1><?php echo __('Geographical approach',null,'menu') ?></h1>
  </div>
<?php include_partial('show_criterias') ?>
<?php include_partial('chart') ?>
<div class="clear"></div>
</div>
