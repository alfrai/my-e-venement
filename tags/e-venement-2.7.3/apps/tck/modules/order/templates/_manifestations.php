<?php
  $manifs = array();
  foreach ( $order->Transaction->Tickets as $ticket )
    $manifs[$ticket->Manifestation->happens_at.' '.$ticket->manifestation_id] =
      cross_app_link_to($ticket->Manifestation->getShortName(),'event','manifestation/show?id='.$ticket->manifestation_id);
  sort($manifs);
?>
<?php echo implode('<br/>',array_reverse($manifs)) ?>
