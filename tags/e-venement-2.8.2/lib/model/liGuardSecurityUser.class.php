<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
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
    
    // lang
    if ( isset($_COOKIE['lang']) )
    {
      $cultures = sfConfig::get('project_internals_cultures', array('fr' => 'Français'));
      if ( isset($cultures[$_COOKIE['lang']]) )
        $this->setCulture($_COOKIE['lang']);
    }
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
  
  public function getContact()
  {
    if (!( $this->getGuardUser() instanceOf sfGuardUser ))
      return false;
    if ( $this->getGuardUser()->Contact->count() == 0 )
      return false;
    
    return $this->getGuardUser()->Contact[0];
  }
  public function getContactId()
  {
    if ( $this->getContact() )
      return $this->getContact()->id;
    return 0;
  }
  
  public function getJabber($i = NULL)
  {
    if ( $this->getGuardUser() instanceOf sfGuardUser )
    {
      if ( intval($i).'' == $i.'' )
        return $this->getGuardUser()->Jabber[$i];
      else
        return $this->getGuardUser()->Jabber;
    }
    return null;
  }
}
