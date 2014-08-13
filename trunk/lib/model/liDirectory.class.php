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

class liDirectory
{
  protected $path = '', $search = NULL;
  
  public static function create($path)
  {
    return new self($path);
  }
  public function __construct($path)
  {
    if ( !is_dir($path) )
      new liFilesystemException('No such directory.');
    
    $this->path = $path;
  }
  
  // case insensitive
  public function ls($search = '*', $sort = 'asc')
  {
    $arr = array('asc' => SCANDIR_SORT_ASCENDING, 'desc' => SCANDIR_SORT_DESCENDING, NULL => SCANDIR_SORT_NONE, '' => SCANDIR_SORT_NONE);
    $search = '/'.str_replace(array('..', '/', '.', '*', '?',), array('', '', '\.', '.*', '.',), $this->search ? $this->search : $search).'/i';
    
    $r = array();
    foreach ( scandir($this->path, isset($arr[$sort]) ? $arr[$sort] : SCANDIR_SORT_ASCENDING) as $filename )
    if ( preg_match($search, $filename) === 1 && is_readable($this->getFilePath($filename)) )
      $r[$this->getFilePath($filename)] = $filename;
    
    return $r;
  }
  public function restrictListedFiles($search)
  {
    $this->search = $search;
    return $this;
  }
  public function getPath()
  {
    return $this->path;
  }
  
  public function getFilePath($filename)
  {
    return $this->path.'/'.$filename;
  }
  public function getFileLastModification($filename)
  {
    return filemtime($this->getFilePath($filename));
  }
  public function getFileLastAccess($filename)
  {
    return fileatime($this->getFilePath($filename));
  }
  public function fileSizeHR($filename)
  {
    $bytes = filesize($this->getFilePath($filename));
    $decimals = 1;
    
    $size = array(' o',' Ko',' Mo',' Go',' To',' Po',' Eo',' Zo',' Yo');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
  }
}
