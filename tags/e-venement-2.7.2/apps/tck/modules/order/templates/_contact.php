<?php if ($order->Transaction->professional_id): ?>
<?php echo cross_app_link_to($order->Transaction->Professional->Organism,'rp','organism/show?id='.$order->Transaction->Professional->Organism->id) ?>
(<?php echo cross_app_link_to($order->Transaction->Contact,'rp','contact/show?id='.$order->Transaction->contact_id) ?>)
<?php else: ?>
<?php
  $arr = array();
  foreach ( $order->Transaction->Contact->Phonenumbers as $phone )
    $arr[] = (string)$phone;
?>
<?php echo cross_app_link_to($order->Transaction->Contact,'rp','contact/show?id='.$order->Transaction->contact_id,false,null,false,'title="'.implode(', ', $arr).'"') ?>
<?php endif ?>
