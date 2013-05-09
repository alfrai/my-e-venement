<?php

class myUser extends liGuardSecurityUser
{
  public function removeAttribute($name)
  {
    $this->getAttributeHolder()->remove($name);
    return $this;
  }
  public function getId()
  {
    return false;
  }
}
