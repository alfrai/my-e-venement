<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    beta-libs is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with beta-libs; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<p class="actions">
<?php
	$i = 0;
	$actions["add"]		= ++$i;
	$actions["edit"]	= ++$i;
	$actions["view"]	= ++$i;
	$actions["del"]		= ++$i;
	if ( isset($_GET["add"]) )
		$action = $actions["add"];
	elseif ( isset($_GET["edit"]) )
		$action = $actions["edit"];
	else	$action = $actions["view"];
	
	
	$baseurl  = 'ann';

	$can = array();
	$can["edit"]	= is_object($user) ? $user->hasRight($config["right"]["edit"])	: false;
	$can["del"]	= is_object($user) ? $user->hasRight($config["right"]["del"])	: false; 
	$can["add"]	= is_object($user) ? $user->hasRight($config["right"]["add"])	: false;
	
	$siteroot = $_SERVER["DOCUMENT_ROOT"].$config["website"]["root"];
	if ( is_file($siteroot.$baseurl.'/'.($file = "index.php")) )
		echo '<a href="'.$baseurl.'/'.$file.'" '.(strpos($_SERVER["PHP_SELF"],$file) !== false ? 'class="active"' : '').'>Index</a>';
	/*
	if ( is_file($siteroot.$baseurl.'/'.($file = "search.php")) )
		echo '<a href="'.$baseurl.'/'.$file.'" '.(basename($_SERVER["PHP_SELF"]) == $file ? 'class="active"' : '').'>Rechercher</a>';
	*/
	if ( is_file($siteroot.$baseurl.'/'.($file = "new-search.php")) )
		echo '<a href="'.$baseurl.'/'.$file.'" '.(basename($_SERVER["PHP_SELF"]) == $file ? 'class="active"' : '').'>Rechercher</a>';
	if ( is_file($siteroot.$baseurl.'/'.($file = "groups.php")) )
		echo '<a href="'.$baseurl.'/'.$file.'" '.(strpos($_SERVER["PHP_SELF"],$file) !== false ? 'class="active"' : '').'>Groupes</a>';
	
	echo '<span class="space"> </span>';
	
	if ( $id )
	{
		echo '<a href="'.$baseurl.'/fiche.php?id='.$id.'&view" '.($action == $actions["view"] ? 'class="active"' : '').'>Consulter</a>';
		if ( $can["edit"] ) echo '<a href="'.$baseurl.'/fiche.php?id='.$id.'&edit" '.($action == $actions["edit"] ? 'class="active"' : '').'>Modifier</a>';
		if ( $can["del"]  ) echo '<a href="'.$baseurl.'/del.php?id='.$id.'" '.($action == $actions["del"] ? 'class="active"' : '').'>Supprimer</a>';
	}
	else
	{
		echo '<a class="inactive">Consulter</a>';
		if ( $can["edit"] ) echo '<a class="inactive">Modifier</a>';
		if ( $can["del"]  ) echo '<a class="inactive">Supprimer</a>';
	}
	if ( $can["add"] ) echo '<a href="'.$baseurl.'/fiche.php?id='.$id.'&add" class="add'.($action == $actions["add"] ? ' active' : '').'">Créer</a>';
	if ( is_file($siteroot.$baseurl.'/'.($file = "import.php")) )
	if ( $can["add"] ) echo '<a href="'.$baseurl.'/'.$file.'" class="import'.(basename($_SERVER["PHP_SELF"]) == $file ? ' active' : '').'">Importer</a>';
	if ( is_file($siteroot.$baseurl.'/'.($file = "emailing.php")) )
	echo '<a href="'.$baseurl.'/'.$file.'" class="mailing'.(basename($_SERVER["PHP_SELF"]) == $file ? ' active' : '').'">e-Mailing</a>';
	echo '<a href="." class="parent">..</a>';
?>
</p>
