<?php include_partial('attendance/filters',array('form' => $form)) ?>
<div class="ui-widget ui-corner-all ui-widget-content">
  <a name="chart-title"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <?php include_partial('attendance/filters_buttons') ?>
    <h1><?php echo __('Global stats on ticketting') ?></h1>
  </div>
<?php include_partial('stats_all',array('contacts' => $contacts, 'professionals' => $professionals)) ?>
<?php include_partial('stats_legend', array('dates' => $criterias['dates'])) ?>
<div class="clear"></div>
<?php include_partial('stats_perso', array('contacts' => $contacts)) ?>
<?php include_partial('stats_pro', array('professionals' => $professionals)) ?>
<div class="clear"></div>
</div>
<?php echo __("coucou
sdfsdf") ?>
