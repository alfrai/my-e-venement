    <div class="gauges sf_admin_list">
      <h4><?php echo __('Seats') ?></h4>
      <div class="help">
        <span class="ui-icon ui-icon-help floatleft"></span>
        <?php echo __('This only concerns seated gauges and seats. It is not about global free tickets.') ?>
      </div>
      <table>
        <tbody>
          <tr class="sf_admin_row ui-widget-content odd online"><th class="ui-state-default ui-th-column"><?php echo __('Available for online sales') ?></th><td>-</td></tr>
          <tr class="sf_admin_row ui-widget-content offline"><th class="ui-state-default ui-th-column"><?php echo __('Free but not available for online sales') ?></th><td>-</td></tr>
          <tr class="sf_admin_row ui-widget-content odd total"><th class="ui-state-default ui-th-column"><?php echo __('Total') ?></th><td>-</td></tr>
        </tbody>
      </table>
      <p class="comment">(<?php echo __('Users used by online sales') ?>: <?php echo ($users = sfConfig::get('app_manifestation_online_users', array())) ? implode(', ', $users) : __('None') ?>)</p>
    </div>
