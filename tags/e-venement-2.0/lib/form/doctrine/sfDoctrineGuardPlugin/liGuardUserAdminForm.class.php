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
    $this->widgetSchema   ['workspaces_list']->setOption('query',Doctrine::getTable('Workspace')->createQuery('ws',true));
    $this->validatorSchema['workspaces_list']->setOption('query',Doctrine::getTable('Workspace')->createQuery('ws',true));
  }
}
