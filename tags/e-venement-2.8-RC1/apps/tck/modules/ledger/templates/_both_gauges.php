<div class="ui-widget-content ui-corner-all" id="gauges">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h2><?php echo __("Involved gauges list") ?></h2>
  </div>

<table>
<tbody>
<?php $total = array('value' => 0, 'printed' => 0, 'ordered' => 0, 'asked' => 0); $class = $display_ws = false; ?>
<?php foreach ( $gauges as $gauge ): ?>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="manifestation"><?php echo cross_app_link_to($gauge->Manifestation,'event','manifestation/show?id='.$gauge->Manifestation->id) ?></td>
    <td class="workspace"><?php if ( $gauge->nb_ws > 1 ) { echo $gauge->Workspace; $display_ws = true; } ?></td>
    <?php foreach ( $total as $key => $value ): ?>
    <?php if ( $key != 'asked' || sfConfig::get('project_tickets_count_demands',false) ): ?>
    <td class="<?php echo $key ?> nb"><?php echo $gauge->$key; $total[$key] += $gauge->$key ?></td>
    <?php endif ?>
    <?php endforeach ?>
    <td class="total nb"><?php echo $gauge->value - $gauge->printed - $gauge->ordered - (sfConfig::get('project_tickets_count_demands',false) ? $gauge->asked : 0) ?></td>
  </tr>
<?php endforeach ?>
<tbody>
<tfoot>
  <tr class="<?php echo ($class = !$class) ? 'overlined' : '' ?>">
    <td class="manifestation"><?php echo __('Total') ?></td>
    <td class="workspace"></td>
    <?php foreach ( $total as $key => $value ): ?>
    <?php if ( $key != 'asked' || sfConfig::get('project_tickets_count_demands',false) ): ?>
    <td class="<?php echo $key ?> nb"><?php echo $value ?></td>
    <?php endif ?>
    <?php endforeach ?>
    <td class="total nb"><?php echo $total['value'] - $total['printed'] - $total['ordered'] - (sfConfig::get('project_tickets_count_demands',false) ? $total['asked'] : 0) ?></td>
  </tr>
</tfoot>
<thead>
  <tr>
    <td class="manifestation"><?php echo __('Manifestation') ?></td>
    <td class="workspace"><?php if ( $display_ws ) echo __('Workspace') ?></td>
    <?php foreach ( $total as $key => $value ): ?>
    <?php if ( $key == 'value' ) $key = 'Gauge' ?>
    <?php if ( $key != 'asked' || sfConfig::get('project_tickets_count_demands',false) ): ?>
    <td class="<?php echo $key ?>"><?php echo __(ucfirst($key)) ?></td>
    <?php endif ?>
    <?php endforeach ?>
    <td class="total"><?php echo __('Total') ?></td>
  </tr>
</thead>
</table>

</div>
