<?php
  $dates = array();
  foreach ( $event->Manifestations as $manif )
    $dates[format_date($manif->happens_at, 'YYYYMM')] = '<span class="month month-'.format_date($manif->happens_at, 'yyyyMM').'">'.format_date($manif->happens_at, 'MMMM').'</span>';
  ksort($dates);
?>
<?php echo implode(' ', $dates) ?>

