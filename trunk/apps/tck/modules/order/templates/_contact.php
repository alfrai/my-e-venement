<?php if ($order->Transaction->professional_id): ?>
<?php echo cross_app_link_to($order->Transaction->Professional->Organism,'rp','organism/show?id='.$order->Transaction->Professional->Organism->id).' ('.cross_app_link_to($order->Transaction->Contact,'rp','contact/show?id='.$order->Transaction->Contact).')' ?>
<?php else: ?>
<?php
  $arr = array();
  foreach ( $order->Transaction->Contact->Phonenumbers as $phone )
    $arr[] = (string)$phone;
?>
<?php echo cross_app_link_to($order->Transaction->Contact,'rp','contact/show?id='.$order->Transaction->Contact->id,false,null,false,'title="'.implode(', ', $arr).'"') ?>
<?php endif ?>
