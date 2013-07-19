<?php include_partial('attendance/filters',array('form' => $form)) ?>
<?php use_helper('Date') ?>
<div class="ui-widget ui-corner-all ui-widget-content">
  <a name="chart-title"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <?php include_partial('attendance/filters_buttons') ?>
    <h1><?php echo __('Tickets by price',null,'menu') ?></h1>
  </div>
  <?php $criterias = $sf_user->getAttribute('stats.criterias',array(),'admin_module') ?>
  <div class="ui-widget-content show-criterias ui-corner-all">
    <ul>
      <?php if ( isset($criterias['dates']['from']) && isset($criterias['dates']['to']) ): ?>
      <li class="dates">
        <?php echo __('To %%to%% from %%from%%',array(
          '%%from%%'  => format_date($criterias['dates']['from']),
          '%%to%%'    => format_date($criterias['dates']['to']),
        )) ?>
      </li>
      <?php endif ?>
      <?php if ( count($criterias['workspaces_list']) > 0 ): ?>
      <li class="workspaces"><?php echo __('%%nb%% workspace(s)', array( '%%nb%%' => count($criterias['workspaces_list']) )) ?></li>
      <?php endif ?>
      <?php if ( count($criterias['meta_events_list']) > 0 ): ?>
      <li class="workspaces"><?php echo __('%%nb%% meta-event(s)',array( '%%nb%%' => count($criterias['meta_events_list']) )) ?></li>
      <?php endif ?>
      <?php if ( count($criterias['users_list']) > 0 ): ?>
      <li class="workspaces"><?php echo __('%%nb%% user(s)',array( '%%nb%%' => count($criterias['users_list']) )) ?></li>
      <?php endif ?>
    </ul>
  </div>
<?php include_partial('chart_ordered') ?>
<?php include_partial('chart_printed') ?>
<div class="clear"></div>
<?php if ( sfConfig::get('project_count_demands',false) ): ?>
<?php include_partial('chart_asked') ?>
<?php endif ?>
<?php include_partial('chart_all') ?>
<div class="clear"></div>
</div>
