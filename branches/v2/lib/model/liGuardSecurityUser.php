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
    if ( $this->getGuardUser() instanceOf sfGuardUser )
    {
      foreach ( $this->getGroups() as $group )
        $groupnames[] = $group->name;
      return $groupnames;
    }
    else return array();
  }
  public function getId()
  {
    if ( $this->getGuardUser() instanceOf sfGuardUser )
      return $this->getGuardUser()->getId();
    return false;
  }
}
