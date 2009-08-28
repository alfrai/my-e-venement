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
	require("config.php");
	$css = "styles/main.css";

	includeClass("navigation");
	includeClass("bd");
	includeClass("user");

	$nav	= new navigation();
	
	// si on est dans une année précédente, vérif du code de controle
	if ( $config["website"]["old"] )
	{
		$go = ( $_SESSION["code"] == md5($_POST["code"]) );
		unset($_SESSION["code"]);
	}
	else	$go = true;
	
	// Il faut que le login et passwd soient définis en POST et que la
	// page qui nous a mené à ce script soit bien login.php
	if ( !isset($_POST["login"]) || !isset($_POST["passwd"]) || !strstr($_SERVER["HTTP_REFERER"],$config["website"]["base"]."login.php") || !$go )
		$nav->redirect($config["website"]["base"]);
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	$user	= new user($bd,$_POST["login"],$_POST["passwd"]);
	
	$level = $config["right"]["view"];
	includeLib("login-check");
	
	// on vire le code de la session
	if ( $config["website"]["old"] )
		unset($_SESSION["code"]);
	
	$bd->free();
	$nav->redirect($_GET["url"] != "" ? $_GET["url"] : $config["website"]["root"]);
?>
