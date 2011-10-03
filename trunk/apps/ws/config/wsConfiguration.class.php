<?php

class wsConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
    sfConfig::set('sf_app_template_dir', sfConfig::get('sf_apps_dir') . '/templates');
  }
  
  public static function addChecksum($arr)
  {
    $arr['checksum'] = md5(json_encode($arr).sfContext::getInstance()->get('salt'));
    return $arr;
  }
  
  public static function formatData($arr)
  {
    return json_encode(self::addChecksum($arr));
  }
}
