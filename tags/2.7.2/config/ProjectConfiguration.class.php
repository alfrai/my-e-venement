<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2011 Ayoub HIDRI <ayoub.hidri AT gmail.com>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

require_once dirname(__FILE__).'/autoload.inc.php';

class ProjectConfiguration extends sfProjectConfiguration
{
  public $yob;
  public $charset       = array();
  public $transliterate = array('from' => '', 'to' => '');

  protected $routings = array();
 
  
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
    //$this->enablePlugins('sfEasyGMapPlugin');
    $this->enablePlugins('sfiCalCreatorPlugin');
    $this->enablePlugins('liBarcodePlugin');
    $this->enablePlugins('liOfcPlugin');
    $this->enablePlugins('sfDomPDFPlugin');
    $this->enablePlugins('sfWebBrowserPlugin');
    $this->enablePlugins('sfFeed2Plugin');
    $this->enablePlugins('liCardDavPlugin');
    
    $modules = array();
    @include(dirname(__FILE__).'/extra-modules.php');
    foreach ( $modules as $module )
      $this->enablePlugins($module);
    
    $this->loadProjectConfiguration();
    
    // transliteration & hyphenation
    $this->charset       = sfConfig::get('software_internals_charset',array());
    $this->transliterate = sfConfig::get('software_internals_transliterate',array('from' => '', 'to' => ''));
    setlocale(LC_ALL,sfConfig::get('project_locale',sfConfig::get('software_internals_locale'))); // w/o it, sometimes transliteration fails
  }

  protected function loadProjectConfiguration()
  {
    if ($this instanceof sfApplicationConfiguration)
    {
      require_once $this->getConfigCache()->checkConfig('config/project.yml');
      require_once $this->getConfigCache()->checkConfig('config/e-venement.yml');
    }
  }
  
  public function configureDoctrine(Doctrine_Manager $manager)
  {
    $manager->setAttribute(Doctrine_Core::ATTR_QUERY_CLASS, 'liDoctrineQuery');
  }
}
