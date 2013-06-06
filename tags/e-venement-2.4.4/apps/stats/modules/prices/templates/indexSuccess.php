<?php include_partial('attendance/filters',array('form' => $form)) ?>
<div class="ui-widget ui-corner-all ui-widget-content">
  <a name="chart-title"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <?php include_partial('attendance/filters_buttons') ?>
    <h1>Répartition globale</h1>
  </div>
<?php include_partial('chart_all') ?>
<?php include_partial('chart_printed') ?>
<div class="clear"></div>
<?php include_partial('chart_ordered') ?>
<?php include_partial('chart_asked') ?>
<div class="clear"></div>
</div>
