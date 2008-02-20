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
	includeLib("bill");
	includeLib("ttt");
	includeJS("ttt");
	includeJS("ajax");
	includeJS("annu");
	
	if ( $user->evtlevel < $config["evt"]["right"]["mod"] )
	{
		$user->addAlert($msg = "Vous n'avez pas un niveau de droits suffisant pour accéder à cette fonctionnalité");
		$nav->redirect($config["website"]["base"]."evt/bill/",$msg);
	}
	
	$cantgetback = false;
	$subtitle = "Dépôt de billets - Places contingeantées";
	$class = "bill evt depot";
	$spectateur = "Responsable du contingeant";
	$css[] = "evt/styles/jauge.css";
	$css[] = "evt/styles/colors.css.php";
        	
	function pasDeBillet()
	{
		global $user, $bd, $nav;
		
		$user->addAlert("Sans billet demandé, pas d'action possible.");
		$bd->free();
		$nav->redirect($_SERVER["PHP_SELF"]);
	}
	
	$data = array();
	$action = array();
	$data[$name	= "numtransac"]	= $_POST[$name];
	$data[$name	= "manif"]	= $_POST[$name];
	$data[$name	= "client"]	= $_POST[$name];
	$data[$name	= "billet"]	= $_POST[$name];
	$action[$name	= "filled"]	= isset($_POST[$name]);
	$action[$name	= "printed"]	= isset($_POST[$name]);
	
	// récup d'une transaction en marche
	if ( $oldtransac = $_POST["oldtransac"] || isset($_GET["t"]) )
	{
		// numéro de transac
		$data["numtransac"]	= $_POST["oldtransac"] ? $_POST["oldtransac"] : $_GET["t"];
		
		// les manifs sélectionnées
		$query = " SELECT *
			   FROM tickets2print_bytransac('".pg_escape_string($data["numtransac"])."')
			   WHERE transaction IN ( SELECT transaction FROM contingeant )";
		// mauvaise query... la suivante est plus juste et permet de récup toutes les manifs	
		$query = " SELECT manifid, nb, tarif.key AS tarif, reduc
			   FROM transaction AS t, masstickets AS m, tarif
			   WHERE transaction = '".pg_escape_string($data["numtransac"])."'
			     AND t.id = m.transaction
			     AND tarifid = tarif.id
			     AND transaction IN ( SELECT transaction FROM contingeant )";
		$request = new bdRequest($bd,$query);
		$data["manif"]		= array();
		$data["billet"]		= array();
		while ( $rec = $request->getRecordNext() )
		{
			$data["billet"][intval($rec["manifid"])][] = intval($rec["nb"]).$rec["tarif"].( intval($rec["reduc"]) < 10 ? "0".intval($rec["reduc"]) : intval($rec["reduc"]));
			if ( !in_array(intval($rec["manifid"]),$data["manif"]) )
				$data["manif"][] = intval($rec["manifid"]);
		}
		$request->free();
		
		// la personne responsable du dépôt
		$query = " SELECT * FROM contingeant WHERE transaction = '".pg_escape_string($data["numtransac"])."'";
		$request = new bdRequest($bd,$query);
		if ( $rec = $request->getRecord() )
			$data["client"] = intval($rec["fctorgid"]) > 0 ? "prof_".intval($rec["fctorgid"]) : "pers_".intval($rec["personneid"]);
		$request->free();
		
		// pour passer direct à l'étape 3 ... petit pb en cas de retour sur des places contingeantées
		// $action["filled"] = true;
	}
		
	// les pré-requis sont sélectionnés
	//if ( is_array($data["manif"]) &&
	if ( intval(substr($data["client"],5)) > 0 )
	{
		// numéro de transaction
		if ( !isset($data["numtransac"]) )
		{
			$arr = array();
			$arr["accountid"] = $user->getId();
			if ( !$bd->addRecord("transaction", $arr) )
			{
				$user->addAlert("Erreur dans la création de la nouvelle transaction.");
				$bd->free();
				$nav->redirect($_SERVER["PHP_SELF"]);
			}
			
			$data["numtransac"] = $bd->getLastSerial("transaction","id");
		}

		/* Pas de besoin du BdC dans l'immédiat
		// extraction du BdC
		if ( isset($_POST["print"]) )
		{
			includePage("bdc");
			$bd->free();
			exit(0);
		}
		
		// suppression du BdC
		if ( isset($_POST["delbdc"]) )
		{
			if ( $bd->delRecordsSimple("bdc",array("transaction" => $data["numtransac"])) )
				$user->addAlert("Annulation du BdC effectuée.");
			else	$user->addAlert("Impossible d'annuler le BdC !");
		}
		*/
		
		$stage = 2;
		
		// nb de billets pré-réservés
		$nb_billets = 0;
		if ( is_array($data["billet"]) )
		foreach ( $data["billet"] as $manif )
		if ( is_array($manif) )
		foreach ( $manif as $value )
		{
			$resa = preg_tarif($value);
			$nb_billets += abs(intval($resa["nb"]));
		}
		
		// si pas de billets pré-réservés
		if ((( ($action["filled"] && !isset($_POST["stage3"])) || $action["printed"] ) && $nb_billets <= 0 )) // || count($data["manif"]) <= 0 )
			pasDeBillet();
		
		/* stages 3-4 */
		
		if ( $action["printed"] )
			$stage = 4;
			
		if ( $action["filled"] )
			$stage = 3;
		
		if ( $stage < 4 )
			includePage("depot-2");
		else	includePage("depot-4");
	}
	else
	{
		$stage = 1;
		includePage("grp-1");
	}
	
	$bd->free();
?>
