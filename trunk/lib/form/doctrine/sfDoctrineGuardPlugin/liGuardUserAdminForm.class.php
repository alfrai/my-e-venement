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
 * sfGuardUser form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrinePluginFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class liGuardUserAdminForm extends sfGuardUserAdminForm
{
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
    parent::configure();
    
    // don't know why but parent::setup() is called before self::configure() so we need to specify the contact's widget correctly here and not in BaseDoctrineForm::setup()
    $this->widgetSchema   ['contact_id'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Contact',
      'url'   => cross_app_url_for('rp','contact/ajax'),
      'default' => !$this->object->Contact[0]->isNew() ? $this->object->Contact[0]->id : '',
    ));
    $this->validatorSchema['contact_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Contact',
      'required' => false,
    ));
    
    foreach ( array('groups_list', 'prices_list', 'meta_events_list', 'member_cards_list', 'permissions_list') as $key )
      $this->widgetSchema[$key]
        ->setOption('expanded',true)
        ->setOption('order_by',array('name',''));
    
    if ( sfContext::hasInstance() )
    {
      $user = sfContext::getInstance()->getUser();
      $this->widgetSchema['prices_list']->setOption('query',
        Doctrine::getTable('Price')->createQuery('p')
          ->andWhere('pt.lang = ?', $user->getCulture())
      );
    }
    $this->widgetSchema['groups_list']
      ->setOption('method', 'getNameWithDescription')
      ->setOption('renderer_class', NULL);

    $this->validatorSchema['workspaces_list']->setOption('query', $q = Doctrine::getTable('Workspace')->createQuery('ws',true));
    $this->widgetSchema   ['workspaces_list']->setOption('query',$q)
                                             ->setOption('order_by',array('name',''))
                                             ->setOption('expanded', true);
    
    $this->validatorSchema['auth_for_groups_list']->setOption('query',$q = Doctrine::getTable('Group')->createQuery('g')->andWhere('g.sf_guard_user_id IS NULL'));
    $this->widgetSchema   ['auth_for_groups_list']->setOption('query',$q)
                                                  ->setOption('order_by',array('name',''))
                                                  ->setOption('expanded', true);
    $this->validatorSchema['auto_groups_list']->setOption('query',$q = Doctrine::getTable('Group')->createQuery('g')->andWhere('g.sf_guard_user_id IS NULL OR g.sf_guard_user_id = ?', $this->getObject()->getId()));
    $this->widgetSchema   ['auto_groups_list']->setOption('query',$q)
                                              ->setOption('order_by',array('sf_guard_user_id IS NOT NULL, g.name',''))
                                              ->setOption('expanded', true);
    
    $choices = array();
    foreach ( Doctrine::getTable('Contact')->getColumnNames() as $field )
    {
      if ( !in_array($field, array('id','name','vcard_uid','latitude','longitude','sf_guard_user_id','updated_at','created_at','slug','version','confirmed','npai','email_no_newsletter',)) )
        $choices[$field] = __(str_replace('_', ' ', ucfirst(str_replace('_id', '', $field))));
    }
    $defaults = array();
    foreach ( $this->object->RpMandatoryFields as $f )
      $defaults[] = $f->value;
    $this->setDefault('rp_mandatory_fields_list', $defaults);
    $this->widgetSchema   ['rp_mandatory_fields_list'] = new sfWidgetFormChoice(array(
      'expanded'  => true,
      'multiple'  => true,
      'choices'   => $choices,
    ));
    $this->validatorSchema['rp_mandatory_fields_list'] = new sfValidatorChoice(array(
      'multiple'  => true,
      'choices'   => array_keys($choices),
      'required'  => false,
    ));
  }
  
  public function doSave($con = NULL)
  {
    // contact embedded form
    if ( $this->values['contact_id'] )
      $this->object->Contact[0] = Doctrine::getTable('Contact')->fetchOneById($this->values['contact_id']);
    else
      unset($this->object->Contact[0]);
    unset($this->values['contact_id']);
    
    $this->saveRpMandatoryFieldsList($con);
    return parent::doSave($con);
  }
  
  public function saveRpMandatoryFieldsList($con = null)
  {
    if (!$this->isValid())
      throw $this->getErrorSchema();
    
    // somebody has unset this widget
    if (!isset($this->widgetSchema['rp_mandatory_fields_list']))
      return;
    
    if (null === $con)
      $con = $this->getConnection();
    
    $existing = $this->object->RpMandatoryFields->getPrimaryKeys();
    $values = $this->getValue('rp_mandatory_fields_list');
    if (!is_array($values))
      $values = array();
    
    $this->object->RpMandatoryFields->delete();
    foreach ( $values as $value )
    {
      $f = new OptionMandatoryField;
      $f->value = $value;
      $this->object->RpMandatoryFields[] = $f;
    }
  }
}
