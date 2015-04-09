<?php use_javascript('helper-csv') ?>
<a
  href="#"
  title="<?php echo __('Save','','sf_admin') ?>"
  class="record ui-corner-all csv-js"
  onclick="javascript: $(this).closest('.ui-widget-content').find('table').downloadCSV($(this).closest('.ui-widget-header').find('h2').text());"
><span><?php echo __('Save','','sf_admin') ?></span></a>
