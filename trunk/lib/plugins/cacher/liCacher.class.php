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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
class liCacher
{
  protected $data = NULL;
  protected $path = NULL;
  
  static public function create($path)
  {
    return new self($path);
  }
  static public function componePath($uri)
  {
    $uri = preg_replace('![&/]{0,1}refresh([=/]\w*){0,1}!', '', $uri);
    $uri = preg_replace('!/$!', '', $uri);
    
    return sfConfig::get('sf_module_cache_dir').'/'.md5($uri).'.data';
  }
  
  public function __construct($path)
  {
    $this->setPath($path);
  }
  
  public function setPath($path)
  {
    $this->path = $path;
    return $this;
  }
  public function getPath()
  {
    return $this->path;
  }
  
  public function __toString()
  {
    return serialize($this->getData());
  }
  
  public function getData()
  {
    return $this->data;
  }
  public function setData($data)
  {
    $this->data = $data;
    return $this;
  }
  
  public function loadData()
  {
    $this->setData(unserialize(file_get_contents($this->getPath())));
    return $this;
  }
  public function writeData()
  {
    file_put_contents($this->getPath(), (string)$this);
    return $this;
  }
  
  public function useCache($interval = '1 day ago')
  {
    if ( !file_exists($this->getPath()) )
      return false;
    
    $ctime = filectime($this->getPath());
    if (!( $ctime !== false && $ctime > strtotime($interval) ))
      return false;
    return $this->loadData()->getData();
  }
}
