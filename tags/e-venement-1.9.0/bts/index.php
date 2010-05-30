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
*    Copyright (c) 2006-2008 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	/** 
	  * Compatibility layer from e-venement to flyspray
	  * 
	  **/
	
	require("../config.php");
	includeClass("user");
	includeClass("navigation");
	includeClass("bd");
	includeClass("bdRequest");
	
	$user   = &$_SESSION["user"];
	$nav	= new navigation();
	$bd     = new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	// le check du login e-venement
	includeLib("login-check");
	
	$bd->setPath("flyspray,public");
	
	// le check de la possibilité d'aller vers flyspray
	$query = " SELECT * FROM fly_users WHERE user_id = ".$user->getId();
	$request = new bdRequest($bd,$query);
	if ( $request->countRecords() > 0 )
	{
		// récup du passwd md5
		$passwd = $request->getRecord("user_pass");
		
		// les propriétés du cookie à l'identique de flyspray
		$path = $config["website"]["root"]."bts/flyspray/";
		$time = time()+60*60*24*30;
		
		// on met les cookies à proprement parlé
		setcookie("flyspray_userid", $user->getId(), $time, $path);
		setcookie("flyspray_passhash", md5($passwd."12a160c01a3407c1c72f9327f26d4c46"), $time, $path);
		setcookie("flyspray_project", 0, $time, $path);
		
		// le lien avec flyspray
		$redirectnewurl = $path; 
	}
	else
	{
		$user->addAlert("Vous n'avez pas la possibilité d'accéder à la gestion des tickets");
		$request->free();
		$nav->redirect(".");
	}
	$request->free();
	
	includeLib("headers");
	includeLib("footer");
	$bd->free();
?>
