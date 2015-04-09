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
class liWidgetFormInputRange extends sfWidgetFormInput
{
  public function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
    $this->setOption('type', 'range');
    $this->addOption('min', 0);
    $this->addOption('max', 10);
  }
  
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $attributes['min'] = $this->getOption('min');
    $attributes['max'] = $this->getOption('max');
    return parent::render($name, $value, $attributes, $errors);
  }
}
