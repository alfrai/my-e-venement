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
*    Copyright (c) 2008 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	require_once("../../config.php");
	require_once("../config.php");
	$css[]	= "styles/main.css";
	
	$config["website"]["base"] = $_SERVER["DOCUMENT_ROOT"].dirname($_SERVER["PHP_SELF"]);
	
	session_start();

	includeClass("navigation");
	includeClass("bd");
	includeClass("bdRequest/array");
	$nav	= new navigation();
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	$bd->setPath("vel,billeterie,public");
	
	/** traitement du paramétrage **/
	$query	= " SELECT * FROM params";
	$request = new bdRequest($bd,$query);
	
	//valeurs par défaut
	$param = array();
	$param["nbevts"] = 3;
	$param["nbmanifs"] = 4;
	$param["open"] = true;
	
	while ( $rec = $request->getRecordNext() )
		$param[$rec["name"]] = $rec["value"];
	
	$request->free();
?>
