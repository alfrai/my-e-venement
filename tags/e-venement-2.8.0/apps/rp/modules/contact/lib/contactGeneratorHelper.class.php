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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

/**
 * contact module helper.
 *
 * @package    e-venement
 * @subpackage contact
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: helper.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class contactGeneratorHelper extends BaseContactGeneratorHelper
{
  public function linkToExtraAction($params,$contact = NULL)
  {
    if (!key_exists('ui-icon', $params)) $params['ui-icon'] = '';
    
    if ( isset($params['more-icon']) )
    {
      $icon = '<span class="ui-icon ui-icon-'.$params['more-icon'].'"></span>';
      unset($params['more-icon']);
    }
    
    $params['params'] = UIHelper::addClasses($params, '');
    if ( !isset($icon) )
    {
      $params['ui-icon'] = $this->getIcon($params['ui-icon'], $params);
      $icon = UIHelper::addIcon($params);
    }
    
    $params['query_string'] = $contact ? '?id='.$contact->id : '';
    
    return '<li class="sf_admin_action_'.$params['action'].'">'.link_to($icon . __($params['label']), sfContext::getInstance()->getModuleName().'/'.$params['action'].$params['query_string'], $params['params']).'</li>';
  }

  public function linkToDelete($object, $params)
  {
    $params['params'] = UIHelper::arrayToString(array('class' => UIHelper::getClasses($params['params']).' ui-priority-secondary'));

    if ($object->isNew() )
      return '';

    $params['ui-icon'] = $this->getIcon('delete', $params);
    return '<li class="sf_admin_action_delete">'.link_to(UIHelper::addIcon($params) . __($params['label'], array(), 'sf_admin'), $this->getUrlForAction('delete'), $object, array('class' => UIHelper::getClasses($params['params']),'method' => 'delete', 'confirm' => !empty($params['confirm']) ? __($params['confirm'], array(), 'sf_admin') : $params['confirm'])).'</li>';
  }
}
