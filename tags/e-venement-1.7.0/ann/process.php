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
	$query	= " SELECT *
		    FROM ".(isset($_GET["more"]) ? "personne_properso" : "personne")."
		    WHERE nom ILIKE '%' || '".$name_start."' || '%'";
	if ( isset($_GET["pro"]) && isset($_GET["more"]) )
	$query .= " AND fctorgid IS NOT NULL";
	$query .= " ORDER BY nom,prenom";
	$personnes = new bdRequest($bd,$query);
	
	// préparation du tableau
	$data = array();
	$data["ppl"] = array();
	
	// remplissage
	while ( $rec = $personnes->getRecordNext() )
	{
		$data["ppl"][] = array();
		$i = count($data["ppl"]) - 1;
		$data["ppl"][$i]["value"]		= $rec["id"];
		$data["ppl"][$i]["data"]["nom"]		= $rec["nom"] ? $rec["nom"] : "--";
		$data["ppl"][$i]["data"]["prenom"]	= $rec["prenom"] ? $rec["prenom"] : "--";
		$data["ppl"][$i]["data"]["orgid"]	= intval($rec["orgid"]) > 0 ? intval($rec["orgid"]) : 0;
		$data["ppl"][$i]["data"]["orgnom"]	= $rec["orgnom"] ? $rec["orgnom"] : " ";
		$data["ppl"][$i]["data"]["fctid"]	= intval($rec["fctorgid"]);
		$data["ppl"][$i]["data"]["fctdesc"]	= $rec["fctdesc"] ? $rec["fctdesc"] : ( $rec["fcttype"] ? $rec["fcttype"] : " " );
		$data["ppl"][$i]["data"]["npai"]	= $rec["npai"] == 't' ? 'true' : 'false';
	}
	
	// le fichier XML
	$xml = new xmlfile($data,"ttt");
	$xml->generateHeaders();
	echo $xml->getXML();
	
	$personnes->free();
	$bd->free();
?>
