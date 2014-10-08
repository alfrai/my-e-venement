<?php

class pubConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
    $this->dispatcher->connect('pub.transaction_before_creation', array($this, 'triggerTransactionBeforeCreation'));
  }
  
  public function shut()
  {
    if ( !sfConfig::get('app_open',false) )
    {
      header('Content-Type: text/html; charset=utf-8');
      die($this->getEnvironment() == 'dev' ? 'This application is not opened' : sfConfig::get('app_texts_when_closed',''));
    }
  }
  
  public function triggerTransactionBeforeCreation(sfEvent $event)
  {
    $params = $event->getParameters();
    $transaction = $params['transaction'];
    $transaction->send_an_email = true;
  }
}
