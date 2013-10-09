<?php

/**
 * GroupWorkspace form.
 *
 * @package    symfony
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class GroupWorkspaceForm extends BaseGroupWorkspaceForm
{
  public function configure()
  {
    $this->validatorSchema   ['workspace_id']
      ->setOption('query', $q = Doctrine::getTable('Workspace')->createQuery('w')->leftJoin('w.GroupWorkspace gw')->andWhere('gw.id IS NULL'));
    $this->widgetSchema   ['workspace_id']
      ->setOption('order_by', array('name', ''))
      ->setOption('query', $q);
  }
}
