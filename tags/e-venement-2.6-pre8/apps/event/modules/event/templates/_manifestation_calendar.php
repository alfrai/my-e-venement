<?php

// options to give to the manifestation action
$type = isset($type) ? $type : 'event';

// calendar's first day calculation
$now = $time = time();
foreach ( $form->getObject()->Manifestations as $manif )
if ( strtotime($manif->happens_at) > $now ) // if this manifestation happens after now
{
  if ( $time > strtotime($manif->happens_at) || $time <= $now ) // and if this manifestation is anterior than the manifestations processed before, take its date
    $time = strtotime($manif->happens_at);
}
elseif ( $time <= $now ) // if this manifestation happens before now and no manifestation happens after now for the moment
{
  if ( $time < strtotime($manif->happens_at) || $time == $now ) // if this manifestation is the last one ever processed
    $time = strtotime($manif->happens_at);
}

?>
<?php include_partial('calendar/show_calendar',array(
  'urls' => array(
    url_for('manifestation/list?'.$type.'_id='.$form->getObject()->id),
  ),
  'start_date' => date('Y-m-01', $time),
)) ?>
