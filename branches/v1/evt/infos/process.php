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
	includeClass("xmlfile");
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	$name_start = $_GET["s"] ? trim("".pg_escape_string($_GET["s"])) : "A";
	$query = " SELECT id, nom, catdesc
		   FROM evenement_categorie
		   WHERE LOWER(nom) LIKE LOWER('".$name_start."') || '%'
		   ORDER BY catdesc,nom";
	$events = new bdRequest($bd,$query);
	
	// préparation du tableau
	$data = array();
	$data["evt"] = array();
	
	// remplissage
	while ( $rec = $events->getRecordNext() )
	{
		$data["evt"][] = array();
		$i = count($data["evt"]) - 1;
		$data["evt"][$i]["value"] = $rec["id"];
		$data["evt"][$i]["data"][($name = "nom")] = $rec[$name];
		$data["evt"][$i]["data"][($name = "catdesc")] = $rec[$name];
	}
	
	// le fichier XML
	$xml = new xmlfile($data,"ttt");
	$xml->generateHeaders();
	echo $xml->getXML();
	
	$events->free();
	$bd->free();
?>
