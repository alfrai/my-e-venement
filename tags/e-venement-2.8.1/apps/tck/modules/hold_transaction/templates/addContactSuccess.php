<?php
  $pros = array();
  if ( $hold_transaction->Transaction->contact_id )
  foreach ( $hold_transaction->Transaction->Contact->Professionals as $pro )
    $pros[] = array('id' => $pro->id, 'name' => $pro->getFullDesc());
?>
<?php echo json_encode(array(
  'id' => $hold_transaction->id,
  'contact_id' => $hold_transaction->Transaction->contact_id,
  'professionals' => $pros,
)) ?>
