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
	$title	= "e-venement : scolaires & groupes";
	$css	= array();
	$css[]	= "styles/main.css";
	$css[]	= "sco/styles/main.css";
	$css[]	= "sco/styles/print.css";
	require_once("../config.php");
	require_once("config.php");

	includeClass("navigation");
	includeClass("user");
	includeClass("bd/group");
	
	$bd = new groupBd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	$bd->setPath("sco,billeterie,public");
	$nav	= new navigation();
	$user	= &$_SESSION["user"];

	includeLib("login-check");
	
	require_once("secu.php");
	if ($user->scolevel < $config["sco"]["right"]["view"] && !headers_sent() )
	{
		$user->addAlert("Vous n'avez pas le droit d'accéder à ce module.");
		$nav->redirect($config["website"]["base"]);
	}
	
        /** traitement du paramétrage **/
        $query  = " SELECT * FROM params";
        $request = new bdRequest($bd,$query);

        //valeurs par défaut
        $config["sco"]["sql"] = array();

        while ( $rec = $request->getRecordNext() )
                $config["sco"]["sql"][$rec["name"]] = $rec["value"];
?>
