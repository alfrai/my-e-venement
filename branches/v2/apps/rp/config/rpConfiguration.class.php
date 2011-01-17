<?php

class rpConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
    sfConfig::set('sf_app_template_dir', sfConfig::get('sf_apps_dir') . '/templates');
    
    $this->dispatcher->connect('admin.save_object', array($this, 'setSpecialFlash'));
  }
  
  public function setSpecialFlash(sfEvent $event)
  {
    $params = $event->getParameters();
    
    // Email
    if ( $params['object'] instanceof Email )
    if ( $params['object']->not_a_test )
      $event->getSubject()->getUser()->setFlash('success',"Your email have been sent correctly.");
  }
}
