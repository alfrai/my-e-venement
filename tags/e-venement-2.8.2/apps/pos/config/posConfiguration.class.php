<?php

class posConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
    parent::configure();
    sfConfig::set('sf_app_template_dir', sfConfig::get('sf_apps_dir') . '/templates');
    $this->dispatcher->connect('admin.save_object', array($this, 'triggerDeclinationCreation'));
  }
  
  public function triggerDeclinationCreation(sfEvent $event)
  {
    $params = $event->getParameters();
    if (! $params['object'] instanceof ProductDeclination )
      return;
    
    $event->getSubject()->redirect('product/edit?id='.$params['object']->product_id.'#sf_fieldset_declinations');
  }
}
