<?php

/**
 * Authentication form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class RemoteAuthenticationForm extends BaseRemoteAuthenticationForm
{
  public function configure()
  {
    $this->widgetSchema['sf_guard_user_id']->setOption('order_by',array('username',''));
    $this->validatorSchema['key'] = new liValidatorWSKey();
    $this->validatorSchema['key']->setOption('required', false);
    $this->validatorSchema['ipaddress'] = new liValidatorIpAddress(array('required' => true));
  }
  
  public function bind(array $taintedValues = null, array $taintedFiles = null, $auth = false)
  {
    if ( !$auth )
      parent::bind($taintedValues,$taintedFiles);
    else
    {
      $key = $taintedValues['key'];
      $ipaddr = $taintedValues['ipaddress'];
      
      $this->isBound = false;
      if ( !$key )
        return;
      
      $q = Doctrine::getTable('RemoteAuthentication')->createQuery('ra')
        ->andWhere('ipaddress = ?',$ipaddr)
        ->andWhere('active');
      $ra = $q->execute();
      
      if ( $ra->count() > 0 )
      foreach ( $ra as $auth )
      if ( md5($auth->User->username.$auth->User->password.$auth->salt) == $key )
      {
        $user = sfContext::getInstance()->getUser();
        $user->setAttribute('salt',$auth->salt);
        $user->setAttribute('ws_id',$auth->User->id);
        
        $this->isBound = true;
        return;
      }
    }
  }
}
