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
	require_once(dirname(__FILE__).'/config.default.php');
	require_once(dirname(__FILE__).'/../config.php');

	includeClass("navigation");
	includeClass("user");
	includeClass("bd/array");
	includeClass("bdRequest/array");
	includeLib(dirname(__FILE__)."/libs/functions");
	
	$nav	= new navigation();
	
	if ( !in_array('vel',$config['mods']) )
  {
    $nav->httpStatus(404);
    die();
  }
  
	$bd	= new arrayBd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	$bd->setPath("vel,billeterie,public");
	
	//require_once(dirname(__FILE__).'/../evt/config.default.php');
	
	$query = "SELECT a.id AS accountid, md5(a.login||a.password||auth.salt) = '".pg_escape_string($_GET['key'])."' AS authenticated, auth.salt
	          FROM account a
	          LEFT JOIN authentication auth ON auth.accountid = a.id
	          WHERE auth.ip = '".pg_escape_string($_SERVER['REMOTE_ADDR'])."'
	          LIMIT 1";
	$request = new bdRequest($bd,$query);
	$auth = $request->getRecord('authenticated') == 't' ? true : false;
	$salt = $request->getRecord('salt');
	$accountid = $request->getRecord('accountid');
	$request->free();
	
	if ( !$auth )
    $nav->httpStatus(403);
?>
