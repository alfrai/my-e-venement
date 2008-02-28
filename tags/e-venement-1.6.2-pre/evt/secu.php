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
	global $user,$bd,$config,$nav;
	require_once($_SERVER["DOCUMENT_ROOT"].$config["website"]["root"]."config.php");
	require_once($_SERVER["DOCUMENT_ROOT"].$config["website"]["root"]."evt/config.php");
	includeClass("bdRequest");
	includeClass("bd");
	
	// module inactif
	if ( !in_array("evt",$config["mods"]) )
	{
		$user->addAlert("Module inexistant");
		$nav->redirect($config["website"]["base"],"module désactivé");
	}
	
	if ( !is_object($bd) )
	{
		$bd = new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
		$bd->setPath("billeterie,public");
	}
	
	if ( !isset($user->evtlevel) )
	{
		$query	= " SELECT level FROM billeterie.rights WHERE id = ".$user->getId();
		$request = new bdRequest($bd,$query);
		$user->evtlevel = intval($request->getRecord("level"));
		$request->free();
	}
	
	if ( $user->evtlevel < $config["evt"]["right"]["view"] && !$user->hasRight($config["right"]["param"]) && !headers_sent() )
	{
		$user->addAlert($msg = "Vous n'avez pas le droit de visionner cette page");
		$nav->redirect($config["website"]["base"],$msg);
	}
?>
