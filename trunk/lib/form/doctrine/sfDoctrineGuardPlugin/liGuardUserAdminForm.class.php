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
    
    foreach ( array('groups_list', 'meta_events_list', 'prices_list', 'member_cards_list', 'permissions_list') as $key )
      $this->widgetSchema[$key]
        ->setOption('expanded',true)
        ->setOption('order_by',array('name',''));
    
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
  }
  
  public function doSave($con = NULL)
  {
    // contact embedded form
    if ( $this->values['contact_id'] )
      $this->object->Contact[0] = Doctrine::getTable('Contact')->fetchOneById($this->values['contact_id']);
    else
      unset($this->object->Contact[0]);
    unset($this->values['contact_id']);
    
    return parent::doSave($con);
  }
}
