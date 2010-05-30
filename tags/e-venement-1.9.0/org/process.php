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
	$query = " SELECT id, nom, catdesc, ville
		   FROM organisme_categorie
		   WHERE nom ILIKE '%' || '".$name_start."' || '%'
		   ORDER BY nom,ville";
	$organismes = new bdRequest($bd,$query);
	
	// préparation du tableau
	$data = array();
	$data["ppl"] = array();
	
	// remplissage
	while ( $rec = $organismes->getRecordNext() )
	{
		$data["ppl"][] = array();
		$i = count($data["ppl"]) - 1;
		$data["ppl"][$i]["value"]		= $rec["id"];
		$data["ppl"][$i]["data"]["nom"]		= $rec["nom"] ? $rec["nom"] : "--";
		$data["ppl"][$i]["data"]["catdesc"]	= $rec["catdesc"] ? $rec["catdesc"] : " ";
		$data["ppl"][$i]["data"]["ville"]	= $rec["ville"] ? $rec["ville"] : " ";
	}
	
	// le fichier XML
	$xml = new xmlfile($data,"ttt");
	$xml->generateHeaders();
	echo $xml->getXML();
	
	$organismes->free();
	$bd->free();
?>
