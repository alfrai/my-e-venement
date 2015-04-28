<?php

/**
 * web_origin module configuration.
 *
 * @package    e-venement
 * @subpackage web_origin
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: configuration.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class web_originGeneratorConfiguration extends BaseWeb_originGeneratorConfiguration
{
  public function __construct()
  {
    parent::__construct();
    require_once(__DIR__.'/ua-parser/UserAgentParser.php'); 
  }
}
