<div class="ui-widget ui-corner-all ui-widget-content charts">
  <a name="chart-all"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h2><?php echo __('Global repartition') ?></h2>
  </div>
  <?php
    $values = array(
      'nb' => $contacts['nb'] + $professionals['nb'],
      'tickets' => $contacts['tickets'] + $professionals['tickets'],
      'events'  => $contacts['events'] + $professionals['events'],
    );
    include_partial('stats_element',array('values' => $values));
  ?>
</div>


