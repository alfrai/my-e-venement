<?php

class liGuardSecurityUser extends sfGuardSecurityUser
{
  public function getCredentials()
  {
    return $this->credentials;
  }
  public function getGroupnames()
  {
    $groupnames = array();
    foreach ( $this->getGroups() as $group )
      $groupnames[] = $group->name;
    return $groupnames;
  }
  public function getId()
  {
    if ( is_object($this->getGuardUser()) )
      return $this->getGuardUser()->getId();
    return false;
  }
}
