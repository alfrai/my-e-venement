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
	@include_once("../../config.php");
	@include_once("../config.php");
	global $config;
	global $panier;
	$panier = $_SESSION["panier"];
	
	// on vérifie si on n'est pas sur un accès direct
	if (   $_SERVER["SCRIPT_NAME"] == $config["website"]["root"]."web/inc/extrait-panier.page.php"
	    || !isset($config) )
		exit(1);
	
	echo '<a href="panier.php" id="panier" title="'.count($panier).' billet(s) dans votre panier"><span>'.count($panier).' billet(s) dans votre panier</span></a>';
?>
