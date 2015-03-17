<?php
  $contacts = array();
  foreach ( $organism->Professionals as $pro )
    $contacts[] =
      link_to(
        $pro->Contact,
        'contact/show?id='.$pro->Contact->id,
        array(
          'title' => $pro->contact_email.' '.($pro->name ? $pro->name : $pro->ProfessionalType),
          'class' => $pro->id == $pro->Organism->professional_id ? 'important' : '',
        )
      ).' '.$pro->getRaw('groups_picto');
  echo implode('<br/>',$contacts);
?>
