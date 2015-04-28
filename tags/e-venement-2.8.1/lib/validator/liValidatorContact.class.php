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

class liValidatorContact extends sfValidatorDoctrineChoice
{
  protected function configure($options = array(), $messages = array())
  {
    parent::configure();
    $this->addOption('exists', false);
    
    $this->setOption('multiple', false);
    $this->setOption('model', 'Contact');
    
    $this->addMessage('duplicate', 'A contact with the same informations already exists, try to authenticate or maybe you misspelled your email address...');
  }
  
  public function clean($value = NULL)
  {
    if ( is_null($value) )
    {
      $this->setOption('column', 'confirmed');
      $value = true;
    }
    
    if ( !$this->getOption('exists') )
    {
      // reverse the Exception throwing
      try { $r = parent::clean($value); }
      catch ( sfValidatorError $v )
      {
        if ( $v->getCode() == 'invalid' )
          return $v->getValue();
      }
      throw new sfValidatorError($this, 'duplicate', array('query' => $this->getOption('query')));
    }
    else
      return parent::clean($value);
  }
}
