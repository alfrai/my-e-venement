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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
// adding this to be sure... because it is also included in the config/liClassLoaderPluginConfiguration.class.php file
require_once __DIR__.'/../lib/vendor/ClassLoader.php';
require_once __DIR__.'/../lib/vendor/ApcClassLoader.php';

class liClassLoader
{
  protected $loader;
  
  public function __construct()
  {
    $this->loader = extension_loaded('apc')
      ? new Symfony\Component\ClassLoader\ApcClassLoader('S2A')
      : new Symfony\Component\ClassLoader\ClassLoader()
    ;
  }
  public static function create()
  { return new self; }
  
  /**
    * register a new $namespace to find in $path
    * @param $namespace   string    the namespace to register
    * @param $path        string    where to find the files,
    *
    * eg. to register the Passbook namespace which files are in /path/to/Passbook,
    * do a $loader->register('Passbook', '/path/to/')
    * in fact the last directory must be named as the namespace
    *
    **/
  public function register($namespace, $path)
  {
    $this->loader->addPrefix($namespace, $path);
    $this->loader->register();
    return $this;
  }
}
