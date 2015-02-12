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
          <tr class="sf_admin_row ui-widget-content odd held">
            <th class="ui-state-default ui-th-column held"><?php echo __('Held seats') ?></th>
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
          <tr class="sf_admin_row ui-widget-content odd closed min">
            <th class="ui-state-default ui-th-column closed" rowspan="2"><?php echo __('Closed & free') ?></th>
            <?php include_partial('show_stats_filling_line') ?>
          </tr>
          <tr class="sf_admin_row ui-widget-content odd closed max">
            <?php include_partial('show_stats_filling_line') ?>
          </tr>
        </tbody>
        <thead>
          <tr class="sf_admin_row ui-widget-content">
            <th><?php echo link_to('<span class="ui-icon ui-icon-arrowrefresh-1-s"></span> '.__('Refresh'), 'manifestation/statsFillingData?id='.$form->getObject()->id.'&refresh=true', array('class' => 'fg-button ui-state-default fg-button-icon-left')) ?></th>
            <td class="ui-state-default ui-th-column sos-st-og">
              <?php echo __('State of sales') ?>:
              <span><?php echo __('seats') ?>, <?php echo __('open gauges for online sales') ?></span>
            </td>
            <td class="ui-state-default ui-th-column f-st-og">
              <?php echo __('Filling') ?>:
              <span><?php echo __('seats') ?>, <?php echo __('open gauges for online sales') ?></span>
            </td>
            <td class="ui-state-default ui-th-column sos-st-sg">
              <?php echo __('State of sales') ?>:
              <span><?php echo __('seats') ?>, <?php echo __('open gauges for onsite sales') ?></span>
            </td>
            <td class="ui-state-default ui-th-column f-st-sg">
              <?php echo __('Filling') ?>:
              <span><?php echo __('seats') ?>, <?php echo __('open gauges for onsite sales') ?></span>
            </td>
            <td class="ui-state-default ui-th-column sos-st-ag">
              <?php echo __('State of sales') ?>:
              <span><?php echo __('seats') ?>, <?php echo __('all gauges') ?></span>
            </td>
            <td class="ui-state-default ui-th-column f-st-ag">
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
            <td class="ui-state-default ui-th-column sos-at-sg">
              <?php echo __('State of sales') ?>:
              <span><?php echo __('everything') ?>, <?php echo __('open gauges for onsite sales') ?></span>
            </td>
            <td class="ui-state-default ui-th-column f-at-sg">
              <?php echo __('Filling') ?>:
              <span><?php echo __('everything') ?>, <?php echo __('open gauges for onsite sales') ?></span>
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
      <p class="comment">(<?php echo __('If you find two numbers in a cell, consider the upper number as the pessimistic estimation and the lower number as the optimistic one.') ?>)</p>
    </div>
