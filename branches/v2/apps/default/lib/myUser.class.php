<?php

class myUser extends sfGuardSecurityUser
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
}
