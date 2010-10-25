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
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	$title	= "e-venement : billetterie";
	$css	= array("styles/main.css","evt/styles/main.css");
	$class	= "bill";
	require_once(dirname(__FILE__).'/../../config.php');
  
	includeClass("navigation");
	includeClass("user");
	includeClass("bd/array");
	
	$nav	= new navigation();
	$user	= &$_SESSION["user"];
	
	$bd	= new arrayBd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	$bd->setPath("billeterie,public");
	
	// espaces
	require_once(dirname(__FILE__).'/../config.php');
	if ( $config['evt']['spaces'] )
	{
	  $query = 'SELECT * FROM space WHERE id '.($user->evtspace ? '= '.$user->evtspace : 'IS NULL');
	  $request = new bdRequest($bd,$query);
	  $spacename = $request->getRecord('name') ? $request->getRecord('name') : 'Espace par défaut';
	  $title .= ' ('.$spacename.')';
	  $request->free();
	}
	
	includeLib("login-check");
	require_once(dirname(__FILE__).'/../config.default.php');
?>
