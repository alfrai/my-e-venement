<?php
  $contacts = array();
  foreach ( $organism->Professionals as $pro )
    $contacts[] =
      link_to(
        $pro->Contact,
        'contact/show?id='.$pro->Contact->id,
        array('title' => $pro->contact_email.' '.($pro->name ? $pro->name : $pro->ProfessionalType))
      ).' '.$pro->getRaw('groups_picto');
  echo implode('<br/>',$contacts);
?>
