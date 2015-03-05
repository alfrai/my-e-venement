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
 * Group form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class GroupForm extends BaseGroupForm
{
  public function doSave($con = NULL)
  {
    $picform_name = 'Picture';
    $file = $this->values[$picform_name]['content_file'];
    unset($this->values[$picform_name]['content_file']);
    
    if (!( $file instanceof sfValidatedFile ))
    {
      unset($this->embeddedForms[$picform_name]);
      unset($this->values[$picform_name]);
    }
    else
    {
      // data translation
      $this->values[$picform_name]['content']  = base64_encode(file_get_contents($file->getTempName()));
      $this->values[$picform_name]['name']     = $file->getOriginalName();
      $this->values[$picform_name]['type']     = $file->getType();
      $this->values[$picform_name]['width']    = 24;
      $this->values[$picform_name]['height']   = 16;
      
      // hack to force root object update
      $this->values['updated_at'] = date('Y-m-d H:i:s');
    }
    
    $r = parent::doSave($con);
    
    // the user cannot remove itself if it hasn't the admin-power or admin-users crendentials
    if ( sfContext::hasInstance() )
    {
      $sf_user = sfContext::getInstance()->getUser();
      if ( !$sf_user->hasCredential(array('admin-users', 'admin-power'), false) )
      {
        $q = Doctrine::getTable('GroupUser')->createQuery('gu')
          ->andWhere('gu.sf_guard_user_id = ?', $sf_user->getId())
          ->andWhere('gu.group_id = ?', $this->object->id);
        if ( $q->count() == 0 )
        {
          error_log('add');
          $gu = new GroupUser;
          $gu->group_id = $this->object->id;
          $gu->sf_guard_user_id = $sf_user->getId();
          $gu->save();
        }
      }
    }
    
    return $r;
  }
  
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
    
    $this->widgetSchema['contacts_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Contact',
      'url'   => url_for('contact/ajax'),
      'order_by' => array('name,firstname',''),
    ));
    
    $this->widgetSchema['professionals_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Professional',
      'url'   => url_for('professional/ajax'),
      'method'=> 'getFullName',
      'order_by' => array('c.name,c.firstname,o.name,t.name,p.name',''),
    ));
    $this->widgetSchema['professionals_list']->getJavascripts();
    $this->widgetSchema['professionals_list']->getStylesheets();
    
    $this->widgetSchema['organisms_list'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Organism',
      'url'   => url_for('organism/ajax'),
      'order_by' => array('name,postalcode,city',''),
    ));
    $this->widgetSchema['organisms_list']->getJavascripts();
    $this->widgetSchema['organisms_list']->getStylesheets();
    
    // the group's owner
    $sf_user = sfContext::getInstance()->getUser();
    $this->validatorSchema['sf_guard_user_id'] = new sfValidatorInteger(array(
      'min' => $sf_user->getId(),
      'max' => $sf_user->getId(),
      'required' => true,
    ));
    $choices = array();
    if ( $sf_user->hasCredential('pr-group-common') )
    {
      $this->validatorSchema['sf_guard_user_id']->setOption('required',false);
      $choices[''] = '';
    }
    $choices[$sf_user->getId()] = $sf_user;
    $this->widgetSchema   ['sf_guard_user_id'] = new sfWidgetFormChoice(array(
      'choices'   => $choices,
      'default'   => $this->isNew() ? $sf_user->getId() : $this->getObject()->sf_guard_user_id,
    ));
    
    // pictures & co
    $this->embedRelation('Picture');
    foreach ( array('name', 'type', 'version', 'height', 'width',) as $fieldName )
      unset($this->widgetSchema['Picture'][$fieldName], $this->validatorSchema['Picture'][$fieldName]);
    $this->validatorSchema['Picture']['content_file']->setOption('required',false);
    unset($this->widgetSchema['picture_id'], $this->validatorSchema['picture_id']);
    
    // removing too big widgets
    if ( !$this->object->isNew() )
    foreach ( array('contacts_list', 'professionals_list', 'organisms_list') as $fieldName )
      unset($this->widgetSchema[$fieldName]);
    
    $this->widgetSchema   ['users_list']
      ->setOption('expanded', true)
      ->setOption('query', $q = Doctrine::getTable('sfGuardUser')->createQuery('u')->andWhere('u.is_active = TRUE'))
      ->setOption('order_by', array('u.username, u.last_name, u.first_name', ''));
    $this->validatorSchema['users_list']->setOption('query', $q);
    
    // adding a default user in users list if it is a creation
    if ( $this->object->isNew() && sfContext::hasInstance() )
      $this->object->Users[] = sfContext::getInstance()->getUser()->getGuardUser();
  }
  
  public function removeUsersList()
  {
    unset($this->widgetSchema['users_list']);
    return $this;
  }
}
