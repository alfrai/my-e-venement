<?php

/**
 * Professional form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProfessionalForm extends BaseProfessionalForm
{
  public function configure()
  {
    $this->widgetSchema['professional_type_id']->setOption('order_by',array('name',''));

    $q = Doctrine::getTable('Group')->createQuery('g');
    if ( sfContext::hasInstance() )
    {
      $q->where('(TRUE')
        ->andWhere('g.sf_guard_user_id = ?',sfContext::getInstance()->getUser()->getId());
      if ( sfContext::getInstance()->getUser()->hasCredential('pr-group-common') )
        $q->orWhere('g.sf_guard_user_id IS NULL');
      $q->andWhere('TRUE)');
    }
    $this->widgetSchema   ['groups_list']
      ->setOption('order_by', array('u.id IS NULL DESC, u.username, name',''))
      ->setOption('query', $q);
    $this->validatorSchema['groups_list']
      ->setOption('query', $q);

    parent::configure();
  }

  public function saveGroupsList($con = null)
  {
    $this->correctGroupsListWithCredentials();
    return parent::saveGroupsList($con);
  }
}
