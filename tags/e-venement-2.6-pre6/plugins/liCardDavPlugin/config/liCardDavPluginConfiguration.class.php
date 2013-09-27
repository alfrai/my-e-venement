<?php

/**
 * liCardDavPlugin configuration.
 * 
 * @package     liCardDavPlugin
 * @subpackage  config
 * @author      Baptiste SIMON
 * @version     SVN: $Id: PluginConfiguration.class.php 17207 2009-04-10 15:36:26Z Kris.Wallsmith $
 */
class liCardDavPluginConfiguration extends sfPluginConfiguration
{
  const VERSION = '2.6-pre6';

  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    require_once __DIR__.'/../lib/vendor/sabredav/vendor/autoload.php';
  }
}
