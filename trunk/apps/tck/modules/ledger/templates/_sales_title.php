  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1>
      <?php echo __('Sales Ledger') ?>
      (<?php echo __('from %%from%% to %%to%%',array('%%from%%' => format_date(strtotime($dates[0])), '%%to%%' => format_date(strtotime($dates[1])))) ?>)
    </h1>
  </div>

