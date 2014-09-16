<?php
  $orgs = array();
  foreach ( $contact->Professionals as $pro )
    $orgs[] = '<a href="mailto:'.$pro->Organism->email.'">'.$pro->Organism->email.'</a>';
  echo implode('<br/>',$orgs);
?>
