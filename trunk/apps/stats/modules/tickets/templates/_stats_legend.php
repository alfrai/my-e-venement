<?php use_helper('Date') ?>
<div class="ui-widget ui-corner-all ui-widget-content charts">
  <a name="chart-all"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h2><?php echo __('Legend') ?></h2>
  </div>
  <div class="legend">
    <p>*<?php echo __('<strong>pessimistic estimation</strong>, even better if the standard deviation is low') ?></p>
    <p><?php echo __('From %%from%% to %%to%%',array('%%from%%' => format_date($dates['from']), '%%to%%' => format_date($dates['to']))) ?></p>
  </div>
</div>




