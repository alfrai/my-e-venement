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
	
	$catid = $_GET["c"]."" == "NULL" ? NULL : intval($_GET["c"]);
	$query = " SELECT *
		   FROM organisme_categorie";
	if ( $catid )
		$query .= "   WHERE categorie = ".$catid;
	elseif ( is_null($catid) )
		$query .= "   WHERE categorie IS NULL";
	$query .= "   ORDER BY nom, catdesc";
	$categories = new bdRequest($bd,$query);
	
	// préparation du tableau
	$data = array();
	$data["org"] = array();
	
	// remplissage
	while ( $rec = $categories->getRecordNext() )
	{
		$data["org"][] = array();
		$i = count($data["org"]) - 1;
		$data["org"][$i]["value"] = $rec["id"];
		$data["org"][$i]["data"]["nom"] = $rec["nom"];
		if ( $rec["ville"] ) $data["org"][$i]["data"]["ville"] = $rec["ville"];
		if ( $rec["catdesc"] ) $data["org"][$i]["data"]["categorie"] = $rec["catdesc"];
	}
	
	// le fichier XML
	$xml = new xmlfile($data,"ttt");
	$xml->generateHeaders();
	echo $xml->getXML();
	
	$categories->free();
	$bd->free();
?>
