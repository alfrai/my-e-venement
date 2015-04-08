<?php

/**
 * WorkspaceUserOrdering form.
 *
 * @package    symfony
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class WorkspaceUserOrderingForm extends BaseWorkspaceUserOrderingForm
{
  public function configure()
  {
    $this->validatorSchema['sf_guard_user_id'] = new sfValidatorInteger(array(
      'required' => false,
    ));
    
    $q = Doctrine::getTable('Workspace')->createQuery('w');
    if ( sfContext::hasInstance() )
      $q->andWhere('w.id NOT IN (SELECT wuo.workspace_id FROM WorkspaceUserOrdering wuo WHERE wuo.sf_guard_user_id = ?)',sfContext::getInstance()->getUser()->getId());
    $this->widgetSchema   ['workspace_id']->setOption('query',$q);
    $this->validatorSchema['workspace_id']->setOption('query',$q);
  }
}
