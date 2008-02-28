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
	require_once("conf.inc.php");
	includeClass("navigation");
	
	$nav = new navigation();
	
	// les préconditions
	if ( !$_GET["plname"] || intval($_GET["siteid"]) <= 0
	  || !$_GET["onmap"]["x"] || !$_GET["onmap"]["y"]
	  || !$_GET["size"]["x"] || !$_GET["size"]["y"] )
	{
		$nav->misc("HTTP/1.0 412 Precondition Failed");
		echo "problème interne";
		exit(1);
	}
	
	$arr = array();
	$arr["plname"]	= $_GET["plname"];
	$arr["siteid"]	= intval($_GET["siteid"]);
	$arr["onmapx"]	= $_GET["onmap"]["x"];
	$arr["onmapy"]	= $_GET["onmap"]["y"];
	$arr["width"]	= $_GET["size"]["x"];
	$arr["height"]	= $_GET["size"]["y"];
	if ( @$bd->addRecord("site_plnum",$arr) )
	{
		$nav->misc("HTTP/1.0 201 Created");
		echo $bd->getLastSerial("site_plnum","id");
	}
	else
	{
		$nav->misc("HTTP/1.0 500 Internal Server Error");
		echo "place en doublon ?";
	}
?>
