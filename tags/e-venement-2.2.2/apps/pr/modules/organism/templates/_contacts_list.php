<?php
  $contacts = array();
  foreach ( $organism->Professionals as $pro )
    $contacts[] = link_to($pro->Contact,'contact/show?id='.$pro->Contact->id);
  echo implode(', ',$contacts);
?>
