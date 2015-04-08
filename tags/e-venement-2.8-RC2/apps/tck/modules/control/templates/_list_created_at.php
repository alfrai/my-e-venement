<?php
  echo strtotime($control->created_at) > strtotime('0:00') && strtotime($control->created_at) < strtotime('tomorrow 0:00')
    ? format_date($control->created_at)
    : ''
?>
<?php echo date('H:i:s', strtotime($control->created_at)) ?>
