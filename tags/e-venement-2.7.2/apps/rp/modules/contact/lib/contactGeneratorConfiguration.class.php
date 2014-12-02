<?php

/**
 * contact module configuration.
 *
 * @package    e-venement
 * @subpackage contact
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: configuration.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class contactGeneratorConfiguration extends BaseContactGeneratorConfiguration
{
  public function __construct()
  {
    parent::__construct();
    
    if ( sfConfig::get('app_options_design',false) )
    require_once sfContext::getInstance()->getConfigCache()
      ->checkConfig('modules/contact/config/'.sfConfig::get('app_options_design').'.yml',true);
  }
}
