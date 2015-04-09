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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
abstract class pubUser extends liGuardSecurityUser
{
  /* those modifications are here to allow multiple online sales entry points */
  public function setAttribute($name, $value, $ns = null)
  {
    return parent::setAttribute($this->getAttributeModifiedName($name), $value, $ns);
  }
  public function hasAttribute($name, $ns = null)
  {
    return parent::hasAttribute($this->getAttributeModifiedName($name), $ns);
  }
  public function getAttribute($name, $default = null, $ns = null)
  {
    return parent::getAttribute($this->getAttributeModifiedName($name), $default, $ns);
  }
  protected function getAttributeModifiedName($name)
  {
    return $name;
    if ( $prefix = sfConfig::get('app_user_session_ns', false) )
    {
      if ( is_null($name) )
        $name = '';
      $name = $prefix.'_'.$name;
    }
    return 'pub_'.$name;
  }
}
