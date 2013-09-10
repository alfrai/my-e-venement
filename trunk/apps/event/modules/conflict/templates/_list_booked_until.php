<?php echo use_helper('Date'); ?>
<?php
  echo format_datetime($manifestation->ends_at > $manifestation->reservation_ends_at
    ? $manifestation->ends_at
    : $manifestation->reservation_ends_at, 'dd/MM/yyyy HH:mm');
?>
