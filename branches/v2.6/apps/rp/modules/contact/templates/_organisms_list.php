<?php
  $action = !$sf_user->hasCredential('pr-organism-edit') ? 'show' : sfContext::getInstance()->getActionName();
  $orgs = array();
  foreach ( $contact->Professionals as $pro )
    $orgs[] = link_to($pro->Organism,'organism/show?id='.$pro->Organism->id);
  echo implode('<br/>',$orgs);
?>
