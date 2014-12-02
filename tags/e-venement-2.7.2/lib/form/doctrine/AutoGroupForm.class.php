<?php

/**
 * AutoGroup form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class AutoGroupForm extends BaseAutoGroupForm
{
  public function configure()
  {
    $sf_user = sfContext::getInstance()->getUser();
    $this->widgetSchema['group_id']->setOption('query', $q = Doctrine::getTable('Group')->createQuery('g')
      ->andWhere('g.sf_guard_user_id = ?'.($sf_user->hasCredential('pr-group-common') ? ' OR g.sf_guard_user_id IS NULL' : ''), $sf_user->getGuardUser()->id)
      ->andWhere('g.id NOT IN (SELECT ag.group_id FROM AutoGroup ag)')
      ->orderBy('u.username IS NOT NULL, u.username, g.name'));
    $this->validatorSchema['group_id']->setOption('query', $q);
  }
}
