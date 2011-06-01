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
	require("conf.inc.php");
	includeClass("bdRequest");
	includeClass("csvExport");
	
	// prerequis
	if ( !$user->hasRight($config["right"]["devel"]) )
	{
		$user->addAlert("Vous n'avez pas les droits nécessaires pour accéder à cette fonctionnalité");
		$nav->redirect($config["website"]["base"]);
	}
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	$query = trim($_POST["req"]);
	$msexcel = isset($_POST["msexcel"]);
	$request = new bdRequest($bd,$query,true);
	
	if ( !trim($_POST["req"]) || $request->hasFailed() )
	{
		if ( is_object($request) )
			$request->free();
		
		$user->addAlert("Requête vide ou incorrecte...");
		$nav->redirect($config["website"]["base"]."?sql");
	}
	
	$arr = array();
	
	// les noms des colonnes
	$arr[] = array();
	$fields = $request->getFields();
	foreach ( $fields as $key => $value )
		$arr[count($arr)-1][] = $key;
	
	// le contenu
	while ( $rec = $request->getRecordNext() )
	{
		$arr[] = array();
		foreach ( $rec as $value )
			$arr[count($arr)-1][] = $value;
	}
	
	$csv = new csvExport($arr,$msexcel);
	$csv->printHeaders('sql.'.date('YmdHis'));
	echo $csv->createCSV();
	
	$request->free();
	$bd->free();
?>
