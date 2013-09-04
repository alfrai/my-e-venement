<?php

// calendar's first day calculation
$now = $time = strtotime('2014-12-20');
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

include_partial('calendar/show_calendar',array(
  'urls' => array(
    url_for('manifestation/list?event_id='.$event->id),
  ),
  'start_date' => date('Y-m-d', $time),
));

?>
