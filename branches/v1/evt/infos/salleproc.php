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
	$query = " SELECT id, nom, ville, SUBSTR(cp,1,2) AS cp
		   FROM site
		   WHERE LOWER(ville) LIKE LOWER('".$name_start."') || '%'
		   ORDER BY nom, cp, ville";
	$events = new bdRequest($bd,$query);
	
	// préparation du tableau
	$data = array();
	$data["salle"] = array();
	
	// remplissage
	while ( $rec = $events->getRecordNext() )
	{
		$data["salle"][] = array();
		$i = count($data["salle"]) - 1;
		$data["salle"][$i]["value"] = $rec["id"];
		$data["salle"][$i]["data"][($name = "nom")] = $rec[$name];
		$data["salle"][$i]["data"][($name = "ville")] = $rec[$name];
		$data["salle"][$i]["data"][($name = "cp")] = $rec[$name] ? $rec[$name] : " ";
	}
	
	// le fichier XML
	$xml = new xmlfile($data,"ttt");
	$xml->generateHeaders();
	echo $xml->getXML();
	
	$events->free();
	$bd->free();
?>
