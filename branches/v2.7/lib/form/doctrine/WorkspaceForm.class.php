<?php

/**
 * Workspace form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class WorkspaceForm extends BaseWorkspaceForm
{
  public function configure()
  {
    unset(
      $this->widgetSchema['manifestations_list'],
      $this->widgetSchema['seated_plans_list']
    );
    
    $this->widgetSchema['users_list']->setOption('expanded', 'true')
      ->setOption('order_by', array('u.username', ''));
    
    $this->widgetSchema['prices_list']->setOption('expanded',true)
      ->setOption('order_by', array('p.name', ''));
    
    if ( !sfContext::getInstance()->getUser()->hasCredential('event-seated-plan') )
      $this->widgetSchema['seated'] = new sfWidgetFormInputHidden;
  }
}
