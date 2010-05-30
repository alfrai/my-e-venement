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
	require_once("../../config.php");
	require_once("../config.php");
	$title	= "Paramétrage de ".$config["divers"]["appli-name"];
	$css	= array("styles/main.css","pro/styles/main.css","def/styles/main.css");

	includeClass("navigation");
	includeClass("user");
	includeClass("bd");
	$nav	= new navigation();
	$user	= &$_SESSION["user"];
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	$bd->setPath("pro,billeterie,public");
	
	includeLib("login-check");
	
	require_once("../secu.php");
	if ( $user->prolevel < $config["pro"]["right"]["param"] && !$user->hasRight($config["right"]["param"]) )
	{
		$user->addAlert("Vous n'avez pas le droit d'accéder à cette partie.");
		$nav->redirect($config["website"]["base"]);
	}
?>
