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
    
    $this->widgetSchema['groups_list']->setOption('order_by',array('name',''));
    $this->widgetSchema['groups_list']->setOption('expanded',true);
    
    $this->widgetSchema['meta_events_list']->setOption('order_by',array('name',''));
    $this->widgetSchema['meta_events_list']->setOption('expanded',true);
    
    $this->widgetSchema   ['workspaces_list']->setOption('query',$q = Doctrine::getTable('Workspace')->createQuery('ws',true)->orderBy('name'));
    $this->widgetSchema   ['workspaces_list']->setOption('expanded',true);
    $this->validatorSchema['workspaces_list']->setOption('query',$q);

    $this->widgetSchema['prices_list']->setOption('order_by',array('name',''));
    $this->widgetSchema['prices_list']->setOption('expanded',true);
  }
}
