<?php

require_once dirname(__FILE__).'../../../../config/autoload.inc.php';

class wsConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
    parent::configure();
    sfConfig::set('sf_app_template_dir', sfConfig::get('sf_apps_dir') . '/templates');
  }
  
  public static function getData($arr)
  {
    if ( !is_array($arr) )
      $r = json_decode($arr,true);
    
    $cs = $r['checksum'];
    unset($r['checksum']);
    
    if ( md5(json_encode($r).sfContext::getInstance()->getUser()->getAttribute('salt')) != $cs )
      throw new sfSecurityException('Unsafe content.');
    return $r;
  }
  
  public static function addChecksum($arr)
  {
    $arr['checksum'] = md5(json_encode($arr).sfContext::getInstance()->getUser()->getAttribute('salt'));
    return $arr;
  }
  public static function formatData($arr)
  {
    return json_encode(self::addChecksum($arr));
  }
  
  public static function authenticate(sfWebRequest $request)
  {
    $auth = new RemoteAuthenticationForm();
    $auth->bind(array('key' => $request->getParameter('key'),'ipaddress' => $request->getRemoteAddress()),array(),true);
    
    if ( !$auth->isValid() )
      throw new sfSecurityException("Unable to login distant service.");
    
    return $auth;
  }
}
