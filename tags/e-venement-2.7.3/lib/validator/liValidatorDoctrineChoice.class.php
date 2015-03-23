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
class liValidatorDoctrineChoice extends sfValidatorDoctrineChoice
{
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);
    $this->addOption('null_value', -1);
  }
  
  protected function doClean($value)
  {
    if (!$this->getOption('multiple'))
      return parent::doClean($value);
    
    if ($query = $this->getOption('query'))
      $query = clone $query;
    else
      $query = Doctrine_Core::getTable($this->getOption('model'))->createQuery();

    if (!is_array($value))
      $value = array($value);

    if (isset($value[0]) && $value !== $this->getOption('null_value') && !$value[0])
      unset($value[0]);

    $count = count($value);

    if ($this->hasOption('min') && $count < $this->getOption('min'))
      throw new sfValidatorError($this, 'min', array('count' => $count, 'min' => $this->getOption('min')));
    if ($this->hasOption('max') && $count > $this->getOption('max'))
      throw new sfValidatorError($this, 'max', array('count' => $count, 'max' => $this->getOption('max')));
    
    foreach ( $value as $i => $v )
    if ( $v === $this->getOption('null_value') )
      $count--;
    
    $query->andWhereIn(sprintf('%s.%s', $query->getRootAlias(), $this->getColumn()), $value);
    
    if ($query->count() != $count)
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    
    return $value;
  }
}
