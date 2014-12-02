<?php

/**
 * Jabber form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class JabberForm extends BaseJabberForm
{
  protected $password = NULL;
  
  public function configure()
  {
    $this->widgetSchema['sf_guard_user_id']
      ->setOption('add_empty', true)
      ->setOption('query', $q = Doctrine_Query::create()->from('SfGuardUser u')
        ->leftJoin('u.Jabber j')
        ->andWhere('j.id IS NULL')
      )
      ->setOption('order_by', array('username', ''))
      ;
    $this->validatorSchema['sf_guard_user_id']->setOption('query', $q);
    
    $this->password = $this->object->password;
    $this->widgetSchema['password'] = new sfWidgetFormInputPassword();
    if ( !$this->object->isNew() )
      $this->validatorSchema['password']->setOption('required', false);
    
    // POWER
    if ( sfContext::hasInstance() && !sfContext::getInstance()->getUser()->hasCredential('admin-power')
      || !$this->object->isNew() )
    {
      unset($this->widgetSchema['sf_guard_user_id'], $this->validatorSchema['sf_guard_user_id']);
    }
  }
  
  public function doSave($con = NULL)
  {
    // POWER
    if ( sfContext::hasInstance()
      && (sfContext::getInstance()->getUser()->hasCredential('admin-power') || !$this->object->sf_guard_user_id) )
      $this->object->sf_guard_user_id = sfContext::getInstance()->getUser()->getId();
    
    // if submitted password is empty, then get back the precedent one
    if ( !$this->object->isNew() && !$this->values['password'] )
    {
      $this->values['password'] = $this->password;
      $this->object->password = $this->password;
    }
    
    return parent::doSave($con);
  }
}
