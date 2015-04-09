  <?php $criterias = $sf_data->getRaw('sf_user')->getAttribute('stats.criterias',array(),'admin_module') ?>
  
  <div class="ui-widget-content show-criterias ui-corner-all">
    <ul>
      <?php if ( isset($criterias['by_tickets']) && $criterias['by_tickets'] === 'y' ): ?>
      <li class="by_tickets"><?php echo __('Counting tickets') ?></li>
      <?php endif ?>
      <?php
        foreach ( array('from' => '- 1 week', 'to' => '+ 3 weeks + 1 day') as $key => $period )
        {
          if ( isset($criterias['dates'][$key]) && is_array($criterias['dates'][$key])
            && $criterias['dates'][$key]['day'] && $criterias['dates'][$key]['month'] && $criterias['dates'][$key]['year'] )
            $criterias['dates'][$key] = strtotime($criterias['dates'][$key]['year'].'-'.$criterias['dates'][$key]['month'].'-'.$criterias['dates'][$key]['day']);
          else
            unset($criterias['dates'][$key]);
        }
      ?>
      <?php if ( isset($criterias['dates']) && is_array($criterias['dates']) ): ?>
      <li class="dates">
        <?php if ( isset($criterias['dates']['from']) && isset($criterias['dates']['to']) ): ?>
        <?php echo __('To %%to%% from %%from%%',array(
          '%%from%%'  => format_date($criterias['dates']['from']),
          '%%to%%'    => format_date($criterias['dates']['to']),
        )) ?>
        <?php elseif ( isset($criterias['dates']['from']) ): ?>
        <?php echo __('From %%from%%',array(
          '%%from%%'  => format_date($criterias['dates']['from']),
        )) ?>
        <?php elseif ( isset($criterias['dates']['to']) ): ?>
        <?php echo __('To %%to%%',array(
          '%%to%%'    => format_date($criterias['dates']['to']),
        )) ?>
        <?php endif ?>
      </li>
      <?php endif ?>
      <?php if ( isset($criterias['workspaces_list']) && count($criterias['workspaces_list']) > 0 ): ?>
      <li class="workspaces"><?php echo __('%%nb%% workspace(s)', array( '%%nb%%' => count($criterias['workspaces_list']) )) ?></li>
      <?php endif ?>
      <?php if ( isset($criterias['meta_events_list']) && count($criterias['meta_events_list']) > 0 ): ?>
      <li class="meta_events"><?php echo __('%%nb%% meta-event(s)',array( '%%nb%%' => count($criterias['meta_events_list']) )) ?></li>
      <?php endif ?>
      <?php if ( isset($criterias['events_list']) && count($criterias['events_list']) > 0 ): ?>
      <li class="events"><?php echo __('%%nb%% event(s)',array( '%%nb%%' => count($criterias['events_list']) )) ?></li>
      <?php endif ?>
      <?php if ( isset($criterias['manifestations_list']) && count($criterias['manifestations_list']) > 0 ): ?>
      <li class="manifestations"><?php echo __('%%nb%% manifestation(s)',array( '%%nb%%' => count($criterias['manifestations_list']) )) ?></li>
      <?php endif ?>
      <?php if ( isset($criterias['users_list']) && count($criterias['users_list']) > 0 ): ?>
      <li class="workspaces"><?php echo __('%%nb%% user(s)',array( '%%nb%%' => count($criterias['users_list']) )) ?></li>
      <?php endif ?>
      <?php if ( isset($criterias['groups_list']) && count($criterias['groups_list']) > 0 ): ?>
      <li class="workspaces"><?php echo __('%%nb%% group(s)',array( '%%nb%%' => count($criterias['groups_list']) )) ?></li>
      <?php endif ?>
    </ul>
  </div>
