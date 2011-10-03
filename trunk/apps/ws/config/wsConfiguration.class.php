<?php

class wsConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
    sfConfig::set('sf_app_template_dir', sfConfig::get('sf_apps_dir') . '/templates');
  }
}
