<?php

class myUser extends sfGuardSecurityUser
{
  public function getCredentials()
  {
    return $this->credentials;
  }
}
