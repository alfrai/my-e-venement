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
	if ( intval($_GET["id"]) <= 0 || !$_GET["onmapx"] || !$_GET["onmapx"] )
	{
		$nav->misc("HTTP/1.0 412 Precondition Failed");
		echo "problème interne";
		exit(1);
	}
	
	$cond		= array();
	$cond["id"]	= intval($_GET["id"]);
	$mod		= array();
	$mod["onmapx"]	= $_GET["onmapx"];
	$mod["onmapy"]	= $_GET["onmapy"];
	if ( $bd->updateRecordsSimple("site_plnum",array("id" => intval($_GET["id"])),$mod) === 1 )
	{
		$nav->misc("HTTP/1.0 202 Accepted");
	}
	else
	{
		$nav->misc("HTTP/1.0 500 Internal Server Error");
		echo $bd->getLastRequest();
		echo "place inexistante ?";
	}
?>
