<?php

require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public $yob;
  
  public function setup()
  {
    // year of birth
    $this->yob = array();
    for ( $i = 0 ; $i < 80 ; $i++ )
      $this->yob[date('Y')-$i] = date('Y') - $i;
    
    $this->enablePlugins('sfDoctrinePlugin');
    $this->enablePlugins('sfFormExtraPlugin');
    $this->enablePlugins('sfDoctrineGraphvizPlugin');
    $this->enablePlugins('sfDoctrineGuardPlugin');
    $this->enablePlugins('sfAdminThemejRollerPlugin');
    $this->enablePlugins('cxFormExtraPlugin');
  }
}
