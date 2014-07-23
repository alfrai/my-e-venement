<?php

class pubConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
  }
  
  public function shut()
  {
    if ( !sfConfig::get('app_open',false) )
      die($this->getEnvironment() == 'dev' ? 'This application is not opened' : sfConfig::get('app_texts_when_closed',''));
  }
}
