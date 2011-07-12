<?php
  $orgs = array();
  foreach ( $contact->Professionals as $pro )
    $orgs[] = link_to($pro->Organism,'organism/show?id='.$pro->Organism->id);
  echo implode(', ',$orgs);
?>
