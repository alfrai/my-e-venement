<?php

class liGuardSecurityUser extends sfGuardSecurityUser
{
  public function __construct(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
  {
    // this is a hack to avoid multiple database requests for the same data
    global $user;
    if ( isset($user) && $user instanceof liGuardSecurityUser )
    {
      foreach ( get_object_vars($user) as $key => $value )
        $this->$key = $value;
      return;
    }
    
    parent::__construct($dispatcher, $storage, $options);
  }
  public function __toString()
  {
    return is_object($this->getGuardUser()) ? $this->getGuardUser()->__toString() : '__unknown__';
  }
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
    return null;
  }
}
