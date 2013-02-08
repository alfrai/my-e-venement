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
    $this->widgetSchema['groups_list']->setOption(
      'order_by',
      array('u.id IS NULL DESC, u.username, name','')
    );
    parent::configure();
  }

  public function saveGroupsList($con = null)
  {
    if ( !sfContext::hasInstance() )
      return parent::saveGroupsList($con);
    $user = sfContext::getInstance()->getUser();
    
    foreach ( $this->object->Groups as $group )
    if ( !$user->hasCredential('pr-group-common') && is_null($group->sf_guard_user_id)
      || $group->sf_guard_user_id != $user->getId() && !is_null($group->sf_guard_user_id) )
    {
      $this->values['groups_list'][] = $group->id;
    }
    return parent::saveGroupsList($con);
  }
}
