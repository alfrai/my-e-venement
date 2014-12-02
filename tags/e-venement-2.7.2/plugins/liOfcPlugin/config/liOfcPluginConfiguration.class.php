<?php
/**
 * liOfcPluginConfiguration.class
 */

/**
 * liOfcPluginConfiguration
 *
 * @author Baptiste SIMON
 * @version 27 mai 2013
 * @package symfony
 * @subpackage liOfcPlugin
 */

class liOfcPluginConfiguration extends sfPluginConfiguration
{
  /**
   * Initialize
   */
  public function initialize()
  {
  	// Plugin dir
    sfConfig::set('li_ofc_root_dir', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..');

    // Core OFC library dir
    sfConfig::set('li_ofc_lib_dir', sfConfig::get('li_ofc_root_dir') . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'ofc');

    // OFC Object
    sfConfig::set('li_ofc_object', sfConfig::get('li_ofc_lib_dir') . DIRECTORY_SEPARATOR . 'open_flash_chart_object.php' );
    
    // OFC data dir
    sfConfig::set('li_ofc_data_dir', sfConfig::get('li_ofc_root_dir') . DIRECTORY_SEPARATOR . 'data');
    
    // liOfcPlugin's images directory
    sfConfig::set('li_ofc_images_dir', sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'liOfcPlugin' . DIRECTORY_SEPARATOR . 'images');
  }
}
