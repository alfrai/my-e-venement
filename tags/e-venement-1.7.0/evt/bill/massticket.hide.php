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
	includeClass("tickets");
	$evt = true;
	$class = "ticket";
	$css[] = "evt/styles/ticket.php";
	
	if ( $user->evtlevel < $config["evt"]["right"]["mod"] )
	{
		$user->addAlert($msg = "Vous n'avez pas un niveau de droits suffisant pour accéder à cette fonctionnalité");
		$nav->redirect($config["website"]["base"]."evt/bill/",$msg);
	}
	
	$manifid = intval($_GET["manifid"]);
	$resa = preg_tarif($_GET["resa"]);
	$transac = $_GET["transac"];		// vérifier que tout est ok pour l'impression
	
	if ( $resa == preg_tarif(NULL) || $manifid <= 0 || !$transac )
	{
		$bd->free();
		exit(0);
	}
	
	$bd->beginTransaction();
	
	// récup des pré_resa à imprimer (et donc à confirmer)
	//	          (SELECT orgnom||' ('||orgville||')' FROM personne_properso WHERE transaction.fctorgid = id) AS deporg
	$bd->updateRecordsRaw("masstickets",
				"tarifid	= tarif.id AND
				 tarif.key	= '".pg_escape_string($resa["tarif"])."' AND
				 reduc		= ".intval($resa["reduc"])." AND
				 manifid	= ".$manifid." AND
				 transaction	= '".pg_escape_string($transac)."'",
				array("printed"	=> "printed + 1"),
				"tarif");
	
	$query	= " SELECT nb, resa.*, tarif.key,
		          (SELECT nom FROM personne WHERE transaction.personneid = id) AS deppers,
		          (SELECT orgnom FROM personne_properso WHERE transaction.fctorgid = fctorgid) AS deporg
		    FROM masstickets AS resa, tarif, transaction
		    WHERE transaction = '".pg_escape_string($transac)."'
		      AND tarifid = tarif.id
		      AND tarif.key = '".pg_escape_string($resa["tarif"])."'
		      AND reduc = ".$resa["reduc"]."
		      AND transaction.id = transaction
		      AND manifid = ".$manifid;
	$request = new bdRequest($bd,$query);
	
	// création du lot de tickets
	$tickets = new Tickets();
	
	$ok = true;
	$ticketid = 0;
	if ( $rec = $request->getRecordNext() )
	{
		$ticketid = intval($rec["transaction"]);
		$arr = array();
		$arr["resa_preid"]	= intval($rec["id"]);
		$arr["accountid"]	= $user->getId();
		
		if ( $transac > 0 )
		{
	// récup des données pour le billet
	$query = "(SELECT evt.nom AS evtnom, evt.typedesc, evt.catdesc, evt.metaevt, preresa.nb,
			   (SELECT nom FROM organisme WHERE evt.organisme1 = id) AS organisme1,
		           (SELECT nom FROM organisme WHERE evt.organisme2 = id) AS organisme2,
		           (SELECT nom FROM organisme WHERE evt.organisme3 = id) AS organisme3,
		           transaction.id AS ticketid, manif.date, site.nom AS sitenom, site.ville AS siteville,
		           tarif.prix, tarif.prixspec, NULL AS organisateur, manif.id AS manifid
		    FROM billeterie.manifestation AS manif, evenement_categorie AS evt,
		         site, masstickets AS preresa, tarif_manif AS tarif, transaction
		    WHERE manif.id = ".$manifid."
		      AND evt.id = manif.evtid
		      AND site.id = manif.siteid
		      AND tarif.manifid = manif.id
		      AND preresa.manifid = manif.id
		      AND preresa.tarifid = tarif.id
		      AND transaction.id = ".$transac."
		      AND transaction.id = transaction
		      AND tarif.key = '".pg_escape_string($resa["tarif"])."'
		      AND manif.id NOT IN (SELECT manifid FROM manif_organisation))
		   UNION
		   (SELECT evt.nom AS evtnom, evt.typedesc, evt.catdesc, evt.metaevt, preresa.nb,
		           (SELECT nom FROM organisme WHERE evt.organisme1 = id) AS organisme1,
		           (SELECT nom FROM organisme WHERE evt.organisme2 = id) AS organisme2,
		           (SELECT nom FROM organisme WHERE evt.organisme3 = id) AS organisme3,
		           transaction.id AS ticketid, manif.date, site.nom AS sitenom, site.ville AS siteville,
		           tarif.prix, tarif.prixspec, organisme.nom AS organisateur, manif.id AS manifid
		    FROM billeterie.manifestation AS manif, evenement_categorie AS evt, organisme, manif_organisation AS orga,
		         site, masstickets AS preresa, tarif_manif AS tarif, transaction
		    WHERE manif.id = ".$manifid."
		      AND evt.id = manif.evtid
		      AND site.id = manif.siteid
		      AND tarif.manifid = manif.id
		      AND orga.manifid = manif.id
		      AND preresa.manifid = manif.id
		      AND preresa.tarifid = tarif.id
		      AND organisme.id = orga.orgid
		      AND transaction.id = ".$transac."
		      AND transaction.id = transaction
		      AND tarif.key = '".pg_escape_string($resa["tarif"])."')";
	//echo $query;
	$ticket = new bdRequest($bd,$query);
	
	if ( $tic = $ticket->getRecordNext() )
	{
		$arr = array();
		if ( $tic["organisme1"] ) $arr[] = $tic["organisme1"];
		if ( $tic["organisme2"] ) $arr[] = $tic["organisme2"];
		if ( $tic["organisme3"] ) $arr[] = $tic["organisme3"];
		
		$bill = array();
		$bill["createurs"]	= implode(" / ",$arr);
		$bill["evtnom"]		= $tic["evtnom"];
		$bill["metaevt"]	= $tic["metaevt"];
		$bill["type"]		= $tic["typedesc"] ? $tic["typedesc"] : $tic["catdesc"];
		$bill["num"]		= intval($tic["ticketid"]);
		$bill["operateur"]	= $user->getId();
		$bill["date"]		= $tic["date"]; 
		$bill["sitenom"]	= $tic["sitenom"];
		$bill["siteville"]	= $tic["siteville"];
		$bill["prix"]		= ($tic["prixspec"] ? $tic["prixspec"] : $tic["prix"])*(1-floatval($resa["reduc"])/100);
		$bill["manifid"]	= $tic["manifid"];

		$nb = intval($tic["nb"]);
		$bill["orga"]		= array();
		if ( $tic["organisateur"] ) $bill["orga"][] = $tic["organisateur"];
		while ( $tic = $ticket->getRecordNext() )
		if ( $tic["organisateur"] )
			$bill["orga"][] = $tic["organisateur"];
		$bill["orga"][] = "Très Tôt Théatre";
		
		$bill["org"]	= implode(" / ",$bill["orga"]);
		
		// le dépot de billetterie
		$bill["depot"] = 'billetterie: '.($rec["deporg"] ? $rec["deporg"] : $rec["deppers"]);
		
		for ( $i = 0 ; $i < $nb ; $i++ )
			$tickets->addToContent($bill);

	} // if ( $tic = $ticket->getRecordNext() )
	
	$ticket->free();
	
	} // if ( $transac > 0 )
	} // while ( $rec = $request->getRecordNext();
	
	$tickets->printAll();
	
	$request->free();
	$bd->endTransaction();
	$bd->free();
?>
