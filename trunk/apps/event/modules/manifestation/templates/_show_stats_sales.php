    <div class="sales sf_admin_list">
      <h4><?php echo __('Sales') ?></h4>
      <table>
        <tbody>
          <tr class="sf_admin_row ui-widget-content odd booked-by-one">
            <th class="ui-state-default ui-th-column"><?php echo __('Tickets prepared & paid by the same user') ?></th>
          </tr>
          <tr class="sf_admin_row ui-widget-content paid-by-one-prepared-by-another">
            <th class="ui-state-default ui-th-column"><?php echo __('Tickets paid by this user but prepared by another') ?></th>
          </tr>
          <tr class="sf_admin_row ui-widget-content odd to-be-paid">
            <th class="ui-state-default ui-th-column"><?php echo __('Tickets prepared by this user, and still unpaid') ?></th>
          </tr>
          <tr class="sf_admin_row ui-widget-content odd seated-to-be-paid">
            <th class="ui-state-default ui-th-column"><?php echo __('Tickets prepared by this user, seated, and still unpaid') ?></th>
          </tr>
        </tbody>
        <thead class="ui-widget-header">
          <tr class="sf_admin_row ui-widget-content">
            <th></th>
          </tr>
        </thead>
      </table>
    </div>
