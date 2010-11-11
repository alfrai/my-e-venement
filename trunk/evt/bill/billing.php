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
	includeJS("jquery");
	includeJS("jquery.evt","evt");
	
	// old system preselection
	$_SESSION['ticket']['old-bill'] = true;
	
	// billetterie express
	if ( isset($_POST["unexpress"]) || isset($_GET["unexpress"]) )
	{
		unset($_SESSION["evt"]["express"]);
		unset($_POST);
	}
	if ( isset($_SESSION["evt"]["express"]["client"]) && isset($_SESSION["evt"]["express"]["manif"])
	  && !isset($_POST["client"]) && !isset($_POST["manif"])
	  && !isset($_GET["t"]) && !isset($_POST["oldtransac"]) )
	{
		if ( !is_array($_POST) ) $_POST = array();
		$_POST["client"] = $_SESSION["evt"]["express"]["client"];
		$_POST["manif"] = $_SESSION["evt"]["express"]["manif"];
	}
	
	if ( $user->evtlevel <= $config["evt"]["right"]["view"] )
	{
		$user->addAlert($msg = "Vous n'avez pas un niveau de droits suffisant pour accéder à cette fonctionnalité");
		$nav->redirect($config["website"]["base"]."evt/bill/",$msg);
	}
	
	$subtitle = "Préparation et vente de billets";
	$class = "bill evt";
	$css[] = "evt/styles/jauge.css";
	$css[] = "evt/styles/colors.css.php";
	$spectateur = "Spectateur";
	
	function pasDeBillet()
	{
		global $user, $bd, $nav;
		
		$user->addAlert("Sans billet demandé, pas de réservation possible.");
		$bd->free();
		$nav->redirect($_SERVER["PHP_SELF"]);
	}
	
	$data = array();
	$action = array();
	if ( isset($_POST[$name = "numtransac"]) ) $data[$name]	= $_POST[$name];
	if ( isset($_POST[$name = "translinked"]) ) $data[$name]	= $_POST[$name];
	$data[$name = "manif"]		= $_POST[$name];
	$data[$name = "client"]		= $_POST[$name];
	$action[$name = "filled"]	= isset($_POST[$name]);
	$action[$name = "printed"]	= isset($_POST[$name]);
	$data["billet"]			= $_POST["billet"];
	
	// récup d'une opération en cours
	$oldtransac = $_POST["oldtransac"] ? $_POST["oldtransac"] : $_GET["t"];
	if ( $oldtransac )
	{
		// gestion de la récup des données de base
		$query	= " SELECT DISTINCT resa.manifid, personne.id AS personneid, personne.fctorgid, transaction.dematerialized, transaction.translinked, transaction.blocked,
			           (SELECT count(*)
			            FROM tickets2print_bytransac('".pg_escape_string($oldtransac)."')
			            WHERE printed = true AND canceled = false) > 0 AS printed,
			           (SELECT count(*)
			            FROM tickets2print_bytransac('".pg_escape_string($oldtransac)."')
			            WHERE printed = true) > 0 AS filled
			    FROM reservation_pre AS resa, personne_properso AS personne, transaction
			    WHERE resa.transaction = '".pg_escape_string($oldtransac)."'
			      AND transaction.id = resa.transaction
			      AND transaction.id NOT IN (SELECT DISTINCT transaction FROM contingeant)
			      AND ( personne.id = transaction.personneid OR (transaction.personneid IS NULL AND personne.id IS NULL) )
			      AND ( personne.fctorgid = transaction.fctorgid
			         OR ( personne.fctorgid IS NULL
			          AND transaction.fctorgid IS NULL ))";
		$request = new bdRequest($bd,$query);
		
		if ( $request->getRecord('blocked') == 't' && $user->evtlevel < $config['evt']['right']['unblock'] )
		{
      $user->addAlert("L'opération visée a été verrouillée, faîtes-la déverrouiller par votre responsable.");
      $nav->redirect(dirname($_SERVER['PHP_SELF']));
	  }
		
		$action["printed"] = true;
		$action["filled"]  = true;
		if ( $rec = $request->getRecord() )
		{
			$data["numtransac"]	= intval($oldtransac);
			$data["client"]		= intval($rec["fctorgid"]) > 0 ? "prof_".intval($rec["fctorgid"]) : "pers_".intval($rec["personneid"]);
			$data["dematerialized"]	= $rec["dematerialized"] == 't';
			$data["translinked"]	= intval($rec["translinked"]);
		}
		while ( $rec = $request->getRecordNext() )
		{
			$action["printed"]	= $rec["printed"] == 't' && $action["printed"];
			$action["filled"]	= ( $rec["filled"]  == 't' && $action["filled"] ) || isset($_GET["filled"]);
			$data["manif"][]	= $rec["manifid"];
		}
		
		// on force le stage...
		if ( $_GET["s"] == 3 ) $action["filled"] = true;
		if ( $_GET["s"] == 4 )
		{
		  $action["filled"]   = false;
		  $action["printed"]  = true;
		}
		
		$request->free();
		
		// billets concernés
		$query = " SELECT *
			   FROM tickets2print_bytransac('".pg_escape_string($oldtransac)."')
			   ORDER BY manifid, tarif, reduc";
		$request = new bdRequest($bd,$query);
		
		$data["billet"] = array();
		while ( $rec = $request->getRecordNext() )
			$data["billet"][intval($rec["manifid"])][]
				= intval($rec["nb"])
				. $rec["tarif"]
				. (intval($rec["reduc"]) < 10 ? intval($rec["reduc"])."0" : intval($rec["reduc"])."");
		
		$request->free();
	}
	
	// récup des billets en placement numéroté
	if ( $config['ticket']['placement'] && isset($data['numtransac']) )
	{
	  $query  = " SELECT tarif.id, tarif.key, pre.reduc, pre.manifid
	              FROM reservation_pre AS pre, tarif
	              WHERE transaction = '".pg_escape_string($data['numtransac'])."'
	                AND tarif.id = pre.tarifid
	                AND plnum IS NOT NULL";
	  $request = new bdRequest($bd,$query);
	  $billets = array();
	  while ( $rec = $request->getRecordNext() )
	  {
	    if ( $rec['reduc'] < 10 )
	      $rec['reduc'] = '0'.$rec['reduc'];
	    $billets[$rec['manifid']][$rec['key'].$rec['reduc']]++;
	  }
	  $request->free();
	  foreach ( $billets as $manif => $tickets )
	  foreach ( $tickets as $key => $value )
	    $data['billet'][$manif][] = $value.$key;
	}
	
	// les pré-requis sont sélectionnés
	if ( isset($data["client"]) )
	//if ( is_array($data["manif"]) )
	{
		// numéro de transaction
		if ( !isset($data["numtransac"]) )
		{
			$arr = array();
			$arr["accountid"] = $user->getId();
	  	$arr['spaceid']     = $user->evtspace ? $user->evtspace : NULL;
			if ( substr($data["client"],0,5) == "pers_" )
				$arr["personneid"] = intval(substr($data["client"],5));
			else
			{
				$proid = intval(substr($data["client"],5));
				$query = " SELECT get_personneid(".$proid.") AS id";
				$request = new bdRequest($bd,$query);
				$persoid = intval($request->getRecord('id'));
				$request->free();
				
				$arr["personneid"]	= $persoid > 0 ? $persoid : NULL;
				$arr["fctorgid"]	  = $proid > 0 ? $proid : NULL;
			}
			if ( !$bd->addRecord("transaction", $arr) )
			{
				$user->addAlert("Impossible de créer la transaction.");
				$bd->free();
				$nav->redirect($_SERVER["PHP_SELF"]);
			}
			$data["numtransac"] = $bd->getLastSerial("transaction","id");
		}
		
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
			if ( $bd->delRecordsSimple("bdc",array("transaction" => $data["numtransac"])) > 0 )
				$user->addAlert("Annulation du BdC effectuée.");
			else	$user->addAlert("Impossible d'annuler le BdC !");
		}
		
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
		if (( $action["printed"] || $action["filled"] ) && abs($nb_billets) == 0 )
			pasDeBillet();
		
		/* stages 4-5 */
		
		// vérif qu'on vient bien du stage 4
		if ( isset($_POST["printed"]) || isset($_POST["paid"]) || isset($_POST["facture"]) || isset($_POST["money"]) )
		{
			// suppression des paiements sélectionnés
			if ( is_array($_POST["paiement"]["del"]) )
			{
				$arr = array();
				foreach ( $_POST["paiement"]["del"] as $value )
				if ( intval($value) > 0 ) $arr[] = intval($value);
				
				if ( count($arr) > 0 )
				{
					$where	= "(id = ".implode(" OR id = ",$arr).")
						   AND transaction = '".pg_escape_string($data["numtransac"])."'";
					if ( !$bd->delRecords("paiement",$where) )
						$user->addAlert("Erreur lors de la suppression des paiements sélectionnés");
				}
			}
			
			// ajout des paiements à ajouter
			if ( is_array($_POST["paiement"]["montant"]) && is_array($_POST["paiement"]["mode"]) )
			foreach($_POST["paiement"]["montant"] as $num => $montant)
			if ( abs(intval($montant)) > 0 && intval($_POST["paiement"]["mode"][$num]) > 0 )
			{
				$arr = array();
				$arr["modepaiementid"]	= intval($_POST["paiement"]["mode"][$num]);
				$arr["montant"]		= floatval($montant);
				$arr["transaction"]	= $data["numtransac"];
				if ( $_POST["paiement"]["date"][$num]["value"] != $_POST["paiement"]["date"]["default"] && $_POST["paiement"]["date"][$num]["value"] )
				$arr["date"]		= date($config["format"]["sysdate"],strtotime($_POST["paiement"]["date"][$num]["value"]));
				
				if ( !$bd->addRecord("paiement",$arr) )
					$user->addAlert("Impossible d'ajouter le paiement de '".$arr["montant"]."' €");
			}
			
			// récupération des paiements déjà effectués
			$query	= " SELECT * FROM paiement WHERE transaction = '".$data["numtransac"]."' ORDER BY id";
			$request = new bdRequest($bd,$query);
			$data["paiement"] = array();
			for ( $i = 0 ; $rec = $request->getRecordNext() ; $i++ )
				$data["paiement"][$i] = array("id" => intval($rec["id"]), "mode" => intval($rec["modepaiementid"]), "montant" => floatval($rec["montant"]), "date" => $rec["date"]);
			$request->free();
			
			// montant réglé
			$given = 0;
			$prices = array();
			foreach ( $data["paiement"] as $value )
				$given += $value["montant"];
			
			// total à payer
			$prices = getPrices($data["numtransac"],true);
			$topay  = $prices[0];
			
			if ( isset($_POST["facture"]) )
			{
				includePage("facture");
				$bd->free();
				exit(0);
			}
			
			// comparaison -> resa terminée ?
			$reste = $topay - $given;
			if (( $reste <= 0 || $topay == 0 ) && isset($_POST["paid"]))
			{
				$stage = 5;
				includePage("grp-5");
				$bd->free();
				exit(0);
			}
			else	$stage = 4;
		}
		
		/* stages 3-4 */
		
		if ( $action["printed"] )
			$stage = 4;
			
		if ( $action["filled"] )
			$stage = 3;
		
		if ( !isset($prices) && $stage != 2 )
			$prices = getPrices($data["numtransac"]);
		
		// enregistrement des infos pour des transactions express
		if ( isset($_POST["express"]) )
		{
			$_SESSION["evt"]["express"]["client"] = $data["client"];
			$_SESSION["evt"]["express"]["manif"] = $data["manif"];
		}
		
		includePage("grp-2");
	}
	else
	{
		$stage = 1;
		includePage("grp-1");
	}
	
	$bd->free();
?>
