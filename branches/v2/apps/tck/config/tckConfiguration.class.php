<?php

class tckConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
    sfConfig::set('sf_app_template_dir', sfConfig::get('sf_apps_dir') . '/templates');
    
    $this->dispatcher->connect('admin.save_object', array($this, 'setSpecialFlash'));
    $this->dispatcher->connect('user.change_authentication', array($this, 'logAuthentication'));
  }
  
  public function logAuthentication(sfEvent $event)
  {
    $params   = $event->getParameters();
    $user     = sfContext::getInstance()->getUser();
    $request  = sfContext::getInstance()->getRequest();
    
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
