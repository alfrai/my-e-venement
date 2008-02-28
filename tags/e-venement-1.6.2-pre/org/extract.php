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
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	if ( count($_POST["id"]) > 0 && is_array($_POST["id"]) && count($_POST["fields"]) > 0 && is_array($_POST["fields"]) )
	{
		// définition de la requete
		$fields = array();
		foreach ( $_POST["fields"] as $value )
			$fields[] = pg_escape_string($value);		// echappement champs sélectionnés
		$query	= ' SELECT DISTINCT ';
		$query .= '"'.implode('","',$fields).'"';
		$query .= " FROM organisme_extractor
			    WHERE ";
		$query .= ' id = '.implode(" OR id = ",$_POST["id"]);
		$request = new bdRequest($bd,$query);
		
		// options
		$printfields	= $_POST["printfields"] == "yes";
		$msexcel	= $_POST["msexcel"] == "yes";
		
		// construction et nettoyage des données
		$arr = $printfields ? array_merge( array($_POST["fields"]), $request->getAllRecords() ) : $request->getAllRecords();
		foreach ( $arr as $line => $lcontent )
		foreach ( $lcontent as $col => $value )
		if ( is_string($value) )
			$arr[$line][$col] = trim($value);
		
		// export
		$csv = new csvExport($arr,$msexcel);
		$csv->printHeaders($user->getLogin().'.'.date('YmdHis'));
		echo $csv->createCSV();
		$request->free();
		$bd->free();
	}
	else
	{
		$user->addAlert("Erreur lors de l'extraction, contactez votre administrateur.");
		$bd->free();
		$nav->redirect($_SERVER["HTTP_REFERER"]);
	}
?>
