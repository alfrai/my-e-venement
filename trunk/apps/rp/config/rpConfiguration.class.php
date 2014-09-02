<?php

require_once dirname(__FILE__).'../../../../config/autoload.inc.php';

class rpConfiguration extends sfApplicationConfiguration
{
  public function setup()
  {
    parent::setup();
    $this->enablePlugins(array('liCardDavPlugin'));
  }
  public function configure()
  {
    parent::configure();
    sfConfig::set('sf_app_template_dir', sfConfig::get('sf_apps_dir') . '/templates');
    
    $this->dispatcher->connect('admin.save_object', array($this, 'setSpecialFlash'));
    $this->dispatcher->connect('admin.save_object', array($this, 'addPhoneNumber'));
    $this->dispatcher->connect('user.change_authentication', array($this, 'logAuthentication'));
  }
  
  public static function changeTemplatesDir(sfAction $action)
  {
    if ( sfConfig::get('app_options_design',false) && sfConfig::get(sfConfig::get('app_options_design').'_active',false) )
    {
      $params = $action->getRoute()->getParameters();
      $action->setTemplate('_'.sfConfig::get('app_options_design','').'/'.($action->getTemplate() ? $action->getTemplate() : $params['action']));
    }
  }
  
  public function addPhoneNumber(sfEvent $event)
  {
    $params = $event->getParameters();
    
    // Phone
    if ( $params['object'] instanceof Contact || $params['object'] instanceof Organism )
    {
      if ( $params['object'] instanceof Contact )
      {
        $params = $event->getSubject()->getRequestParameter('contact');
        $pn = new ContactPhonenumber();
        $pn->contact_id = $event->getSubject()->contact->id;
      }
      else
      {
        $params = $event->getSubject()->getRequestParameter('organism');
        $pn = new OrganismPhonenumber();
        $pn->organism_id = $event->getSubject()->organism->id;
      }
      
      if ( $event->getSubject()->form->isValid() && isset($params['phone_number']) && $params['phone_number'] )
      {
        $pn->name = $params['phone_type'];
        $pn->number = $params['phone_number'];
        $pn->save();
      }
    }
  }
  public function setSpecialFlash(sfEvent $event)
  {
    $params = $event->getParameters();
    
    // Email
    if ( $params['object'] instanceof Email )
    if ( $params['object']->not_a_test )
      $event->getSubject()->getUser()->setFlash('success',"Your email have been sent correctly.");
  }
  public function logAuthentication(sfEvent $event)
  {
    $params   = $event->getParameters();
    $user     = sfContext::getInstance()->getUser();
    $request  = sfContext::getInstance()->getRequest();
    
    if ( !is_object($user) )
      return false;
    
    if (( sfConfig::get('project_login_alert_beginning_at', false) && sfConfig::get('project_login_alert_beginning_at') < time() || !sfConfig::get('project_login_alert_beginning_at', false) )
      &&( sfConfig::get('project_login_alert_ending_at', false) && sfConfig::get('project_login_alert_ending_at') > time() || !sfConfig::get('project_login_alert_ending_at', false) )
      && sfConfig::get('project_login_alert_message', false) )
      $user->setFlash('error', sfConfig::get('project_login_alert_message'));

    $auth = new Authentication();
    $auth->sf_guard_user_id = $user->getId();
    $auth->description      = $user;
    $auth->ip_address       = $request->getHttpHeader('addr','remote');
    $auth->user_agent       = $request->getHttpHeader('User-Agent');
    $auth->referer          = $request->getReferer();
    $auth->success          = $params['authenticated'];
    
    $auth->save();
  }
}
