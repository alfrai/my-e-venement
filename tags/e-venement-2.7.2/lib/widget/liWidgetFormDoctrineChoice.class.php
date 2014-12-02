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
/**
 * liWidgetFormDoctrineChoice permits adding an active "add_empty" configuration option if the option "multiple" is set
 * This is particularly interesting in filters because the combination of this special add_empty option and the multiple one permits 2 behaviours:
 * - this parameter doesn't matter (nothing is selected)
 * - I want all the records that has an empty property (aka NULL)
 * - I want all the records that has a property in this selected set
 * 
 * This "add_empty" option has to be based on this model :
 * array(
 *   '-1',
 *   'No property'
 * );
 *
 * The rank 1 of this array is used for what you want to display
 * The rank 0 of this array is used for the key value
 *
 * If the option "multiple" is not set, then this behaviour is useless and the rank 1 alone is used
 *
 * @package    symfony
 * @subpackage widget
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfWidgetFormPropelJQueryAutocompleter.class.php 12130 2008-10-10 14:51:07Z fabien $
 */
class liWidgetFormDoctrineChoice extends sfWidgetFormDoctrineChoice
{
  /**
   * Returns the choices associated to the model.
   *
   * @return array An array of choices
   */
  public function getChoices()
  {
    $choices = parent::getChoices();
    
    $empty = $this->getOption('add_empty');
    if ( false !== $empty && is_array($empty) )
    {
      $shown = true === $this->getOption('add_empty') ? '' : $this->translate($empty[1]);
      if ( !$this->getOption('multiple') || !isset($empty[0]) )
        $choices[''] = $shown;
      else
      {
        unset($choices['']);
        $choices
          = array($empty[0] => $shown)
          + $choices;
      }
    }
    
    return $choices;
  }
}

