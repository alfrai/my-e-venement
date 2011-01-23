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
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public $yob;
  public $charset = array(
    'db' => 'UTF-8',
    'ms' => 'WINDOWS-1252//TRANSLIT',
    'ascii' => 'ASCII//TRANSLIT',
  );
  public $transliterate = array(
    'ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ',
    'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy');

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
  }

  public function generateExternalUrl($args = array('app' => NULL, 'name' => NULL, 'parameters' => array()))
  {
    if ( !isset($args['parameters']) ) $args['parameters'] = array();
    
    // based on e-venement conventions
    $env = sfConfig::get('sf_environment');
    $controller = $args['app'].($env != 'prod' ? '_'.$env : '').'.php';
    
    $dir = dirname($_SERVER['SCRIPT_NAME']) === '/' ? '/' : dirname($_SERVER['SCRIPT_NAME']).'/';
    
    return $args['app']
      ? $dir.$controller.$this->getNewRouting($args['app'])->generate($args['name'], $args['parameters'])
      : false;
  }
 
  public function getNewRouting($app)
  {
    if ( !$app )
      return false;
    
    if (!isset($this->routings[$app]))
    {
      $this->routings[$app] = new sfPatternRouting(new sfEventDispatcher());
      $config = new sfRoutingConfigHandler();
      $routes = $config->evaluate(array(sfConfig::get('sf_apps_dir').'/'.$app.'/config/routing.yml'));
      $this->routings[$app]->setRoutes($routes);
    }
 
    return $this->routings[$app];
  }




}
