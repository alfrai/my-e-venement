    <div class="filling-complete sf_admin_list">
      <h4><?php echo __('Filling') ?></h4>
      <table>
        <tbody>
          <tr class="sf_admin_row ui-widget-content odd free min">
            <th class="ui-state-default ui-th-column" rowspan="2"><?php echo __('Free') ?></th>
            <?php include_partial('show_stats_filling_line') ?>
          </tr>
          <tr class="sf_admin_row ui-widget-content odd free max">
            <?php include_partial('show_stats_filling_line') ?>
          </tr>
          <tr class="sf_admin_row ui-widget-content ordered">
            <th class="ui-state-default ui-th-column ordered"><?php echo __('Ordered') ?></th>
            <?php include_partial('show_stats_filling_line') ?>
          </tr>
          <tr class="sf_admin_row ui-widget-content odd printed">
            <th class="ui-state-default ui-th-column printed"><?php echo __('Sold') ?></th>
            <?php include_partial('show_stats_filling_line') ?>
          </tr>
          <tr class="sf_admin_row ui-widget-content total min">
            <th class="ui-state-default ui-th-column total" rowspan="2"><?php echo __('Total') ?></th>
            <?php include_partial('show_stats_filling_line') ?>
          </tr>
          <tr class="sf_admin_row ui-widget-content total max">
            <?php include_partial('show_stats_filling_line') ?>
          </tr>
          <tr class="sf_admin_row ui-widget-content odd not-free">
            <th class="ui-state-default ui-th-column not-free"><?php echo __('Not free') ?></th>
            <?php include_partial('show_stats_filling_line') ?>
          </tr>
          <tr class="sf_admin_row ui-widget-content odd free min">
            <th class="ui-state-default ui-th-column" rowspan="2"><?php echo __('Free') ?></th>
            <?php include_partial('show_stats_filling_line') ?>
          </tr>
          <tr class="sf_admin_row ui-widget-content odd free max">
            <?php include_partial('show_stats_filling_line') ?>
          </tr>
        </tbody>
        <thead>
          <tr class="sf_admin_row ui-widget-content">
            <th></th>
            <td class="ui-state-default ui-th-column sos-s-og">
              <?php echo __('State of sales') ?>:
              <span><?php echo __('seats') ?>, <?php echo __('open gauges for online sales') ?></span>
            </td>
            <td class="ui-state-default ui-th-column f-s-og">
              <?php echo __('Filling') ?>:
              <span><?php echo __('seats') ?>, <?php echo __('open gauges for online sales') ?></span>
            </td>
            <td class="ui-state-default ui-th-column sos-s-ag">
              <?php echo __('State of sales') ?>:
              <span><?php echo __('seats') ?>, <?php echo __('all gauges') ?></span>
            </td>
            <td class="ui-state-default ui-th-column f-s-ag">
              <?php echo __('Filling') ?>:
              <span><?php echo __('seats') ?>, <?php echo __('all gauges') ?></span>
            </td>
            <td class="ui-state-default ui-th-column sos-at-og">
              <?php echo __('State of sales') ?>:
              <span><?php echo __('everything') ?>, <?php echo __('open gauges for online sales') ?></span>
            </td>
            <td class="ui-state-default ui-th-column f-at-og">
              <?php echo __('Filling') ?>:
              <span><?php echo __('everything') ?>, <?php echo __('open gauges for online sales') ?></span>
            </td>
            <td class="ui-state-default ui-th-column sos-at-ag">
              <?php echo __('State of sales') ?>:
              <span><?php echo __('everything') ?>, <?php echo __('all gauges') ?></span>
            </td>
            <td class="ui-state-default ui-th-column f-at-ag">
              <?php echo __('Filling') ?>:
              <span><?php echo __('everything') ?>, <?php echo __('all gauges') ?></span>
            </td>
          </tr>
        </thead>
      </table>
      <p class="comment">(<?php echo __('Users used by online sales') ?>: <?php echo ($users = sfConfig::get('app_manifestation_online_users', array())) ? implode(', ', $users) : __('None') ?>)</p>
    </div>
