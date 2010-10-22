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
*    Copyright (c) 2006-2007 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	require("config.php");
	includeClass("navigation");
	includeClass("user");
	
	$nav = new Navigation();
	$user = &$_SESSION["user"];
	$level = $config["right"]["archives"];
		
	includeLib("login-check");
	$user->delog();
	
	$nav->redirect($config["website"]["base"].$config["website"]["dirtopast"]."/".urlencode($_GET["past"])."/login.php","Changement d'année");
?>
