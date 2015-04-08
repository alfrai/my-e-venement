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

interface liGarbageCollectorInterface
{
  /**
    * intialize the garbage collectors
    *
    * @return sfApplicationConfiguration      $this
    *
    **/
  public function initGarbageCollectors(sfCommandApplicationTask $task = NULL);
  
  /**
    * get back a specific garbage collector named $name
    *
    * @param $name      string        the name of the garbage collector that will be returned
    * @return           Closure|FALSE the collector called $name, or false if it does not exist
    *
    **/
  public function getGarbageCollector($name);
  
  /**
    * add a new collector to the object's collection
    *
    * @param $name      string        the name of the new garbage collector
    * @return           sfApplicationConfiguration $this
    * @throws           liEvenementException if a collector already exists with this $name
    *
    **/
  public function addGarbageCollector($name, Closure $function);
  
  /**
    * add or replace a collector to the object's collection
    *
    * @param $name      string        the name of the new garbage collector
    * @return           sfApplicationConfiguration $this
    *
    **/
  public function addOrReplaceGarbageCollector($name, Closure $function);
  
  /**
    * executes one or more collectors
    *
    * @param $names     string|array the names of the garbage collectors to run
    * @return           sfApplicationConfiguration $this
    *
    **/
  public function executeGarbageCollectors($names);
}
