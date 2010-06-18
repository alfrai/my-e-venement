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
*    Copyright (c) 2006-2007 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	require("conf.inc.php");
	includeLib("bill");
	includeClass("tickets");
	
	// vérif des droits
	if ( $user->evtlevel < $config["evt"]["right"]["mod"] )
	{
		$user->addAlert($msg = "Vous n'avez pas un niveau de droits suffisant pour accéder à cette fonctionnalité");
		$nav->redirect($config["website"]["base"]."evt/bill/",$msg);
	}
	
	// récup des paramètres
	$manifid = intval($_GET["manifid"]);
	$resa = preg_tarif($_GET["resa"]);
	$transac = $_GET["transac"];
	
	// vérif bon état des choses
	if ( $resa == preg_tarif(NULL) || $manifid <= 0 || !$transac )
	{
		$bd->free();
		exit(0);
	}
	
	// les places numérotées
	$go = true;
	if ( $config["ticket"]["placement"] )
	{
	  $query = "SELECT resa.*, site_plnum.plname
	            FROM reservation_pre AS resa, manifestation AS manif, site_plnum, tarif
	            WHERE resa.transaction = '".pg_escape_string($transac)."'
	              AND resa.manifid  = manif.id
	              AND manif.siteid  = site_plnum.siteid
	              AND resa.plnum    = site_plnum.id
	              AND tarif.key     = '".pg_escape_string($resa['tarif'])."'
	              AND tarif.id      = resa.tarifid
	              AND resa.reduc    = ".intval($resa['reduc']);
	  $request = new bdRequest($bd,$query);
	  $plnum = array();
	  $places = array(); // pour vérifier les doublons à la commande
	  while ( $rec = $request->getRecordNext() )
	  {
	    $plnum[$rec['id']]['plnum'] = $rec['plnum'];
	    $plnum[$rec['id']]['plname'] = $rec['plname'];
	    $places[$rec['plnum']]++;
	  }
	  $request->free();
	  
	  foreach ( $places as $key => $value )
	  if ( $value > 1 )
	  {
	    $user->addAlert("Impossible de lancer l'impression : vous avez affecté deux billets à la même place.");
	    $go = false;
	    break;
	  }
		
		// on n'imprime rien si il y a une erreur
		if ( !$go )
		{
			$url = parse_url($_SERVER["HTTP_REFERER"]);
			$nav->redirect($url["scheme"]."://".$url["host"].$url["port"].$url["path"]."?t=".urlencode($transac)."&s=3");
		}
	}
	
	// billet groupé ?
	$group = isset($_GET["group"]) && $config["ticket"]["enable_group"] && !is_array($plnum);
	$nbgroup = 0;
	
	$bd->beginTransaction();
	
	// récup du tarifid (optimisation)
	$query = " SELECT get_tarifid(".$manifid.",'".pg_escape_string($resa["tarif"])."') AS tarifid";
	$request = new bdRequest($bd,$query);
	$tarifid = $request->getRecord("tarifid");
	$request->free();
	
	// MAJ des reservation_cur déjà imprimés pour la même chose
	$bd->updateRecords(
	        "reservation_cur",
			    "    reduc = ".$resa["reduc"]."
			     AND pre.id = resa_preid
			     AND tarifid = ".$tarifid."
			     AND manifid = ".$manifid."
			     AND transaction = '".pg_escape_string($transac)."'",
			    array("canceled" => "t"),
			    "reservation_pre AS pre");
	
	// récup des pré_resa à imprimer (et donc à confirmer)
	$query	= " SELECT resa.*, '".pg_escape_string($resa["tarif"])."' AS key, resa.id IN (SELECT resa_preid FROM reservation_cur WHERE canceled = true) AS duplicata
		    FROM reservation_pre AS resa
		    WHERE transaction = '".pg_escape_string($transac)."'
		      AND resa.id NOT IN ( SELECT resa_preid FROM reservation_cur WHERE canceled = false )
		      AND tarifid = ".$tarifid."
		      AND reduc = ".$resa["reduc"]."
		      AND manifid = ".$manifid;
	$request = new bdRequest($bd,$query);
	
	// création du lot de tickets
	$tickets = new Tickets($group);
	
	$ok = true;
	$ticketid = 0;
	for ( $i = 0 ; $rec = $request->getRecordNext() ; $i++ )
	{
		$ticketid = intval($rec["transaction"]);
		$arr = array();
		$arr["resa_preid"]	= $resa_preid = intval($rec["id"]);
		$arr["accountid"]	= $user->getId();
		$ok = $ok && $bd->addRecord("reservation_cur",$arr);
		
		// annulation et placement numéroté
		if ( $rec["annul"] == 't' && $config['ticket']['placement'] && $plnum[$rec['id']]['plnum'] )
		{
		  /*
		  $bd->updateRecords(
		    'reservation_pre',
		    '    reservation_pre.plnum = '.intval($plnum[$rec['id']]['plnum']).'
		     AND reservation_pre.transaction = transaction.translinked
		     AND transaction.id = '.pg_escape_string($transac),
		    array('plnum' => null),
		    'transaction'
		  );
		  */
		  // pour tout virer des placements numérotés semblables à l'annulation en cours
		  $bd->updateRecords(
		    'reservation_pre',
		    'transaction != '.pg_escape_string($transac).' AND reservation_pre.plnum = '.intval($plnum[$rec['id']]['plnum']),
		    array('plnum' => null),
		    'transaction'
		  );
		}
		
		// "impression" du billet
		if ( $transac > 0 && $ok)
		{
			// récup des données pour le billet
			$query = "(SELECT evt.petitnom AS evtnom, evt.nom AS evtbignom, evt.typedesc, evt.metaevt,
				   	   (SELECT libelle FROM evt_categorie WHERE id = evt.categorie) AS catdesc,
				           (SELECT nom FROM organisme WHERE evt.organisme1 = id) AS organisme1,
				           (SELECT nom FROM organisme WHERE evt.organisme2 = id) AS organisme2,
				           (SELECT nom FROM organisme WHERE evt.organisme3 = id) AS organisme3,
				           ".$ticketid." AS ticketid, manif.date, site.nom AS sitenom, site.ville AS siteville,
				           getprice(manif.id,".$tarifid.") AS prix, (SELECT description FROM tarif WHERE id = ".$tarifid.") AS tarif, NULL AS organisateur, manif.id AS manifid
				    FROM billeterie.manifestation AS manif, evenement AS evt,
				         site, reservation_pre AS preresa, reservation_cur AS resa
				    WHERE manif.id = ".$manifid."
				      AND evt.id = manif.evtid
				      AND site.id = manif.siteid
				      AND preresa.manifid = manif.id
				      AND preresa.transaction = '".pg_escape_string($transac)."'
				      AND resa.resa_preid = preresa.id
				      AND manif.id NOT IN (SELECT manifid FROM manif_organisation))
				   UNION
				   (SELECT evt.petitnom AS evtnom, evt.nom AS evtbignom, evt.typedesc, evt.metaevt,
				           (SELECT libelle FROM evt_categorie WHERE id = evt.categorie) AS catdesc,
				           (SELECT nom FROM organisme WHERE evt.organisme1 = id) AS organisme1,
				           (SELECT nom FROM organisme WHERE evt.organisme2 = id) AS organisme2,
				           (SELECT nom FROM organisme WHERE evt.organisme3 = id) AS organisme3,
				           ".$ticketid." AS ticketid, manif.date, site.nom AS sitenom, site.ville AS siteville,
				           getprice(manif.id,".$tarifid.") AS prix, (SELECT description FROM tarif WHERE id = ".$tarifid.") AS tarif, organisme.nom AS organisateur, manif.id AS manifid
				    FROM billeterie.manifestation AS manif, evenement AS evt, organisme, manif_organisation AS orga,
				         site, reservation_pre AS preresa, reservation_cur AS resa
				    WHERE manif.id = ".$manifid."
				      AND evt.id = manif.evtid
				      AND site.id = manif.siteid
				      AND orga.manifid = manif.id
				      AND preresa.manifid = manif.id
				      AND preresa.transaction = '".pg_escape_string($transac)."'
				      AND resa.resa_preid = preresa.id
				      AND organisme.id = orga.orgid)";
			$ticket = new bdRequest($bd,$query);
			
			if ( $tic = $ticket->getRecordNext() )
			{
				$arr = array();
				if ( $tic["organisme1"] ) $arr[] = $tic["organisme1"];
				if ( $tic["organisme2"] ) $arr[] = $tic["organisme2"];
				if ( $tic["organisme3"] ) $arr[] = $tic["organisme3"];
				
				$bill = array();
				if ( $rec["annul"] == 't' )
					$bill["info"]	= "annulation";
				else if ( $rec["duplicata"] == 't' )
					$bill["info"]	= "duplicata";
				$bill["createurs"]	= implode(" / ",$arr);
				$bill["evtnom"]		= $tic["evtnom"] ? $tic["evtnom"] : $tic["evtbignom"];
				$bill["metaevt"]	= $tic["metaevt"];
				$bill["type"]		= $tic["typedesc"] ? $tic["typedesc"] : $tic["catdesc"];
				$bill["num"]		= intval($tic["ticketid"]);
				$bill["operateur"]	= $user->getId();
				$bill["date"]		= $tic["date"];
				$bill["sitenom"]	= $tic["sitenom"];
				$bill["siteville"]	= $tic["siteville"];
				$bill["prix"]		= $tic["prix"]*(1-floatval($resa["reduc"])/100);
				$bill['tarif']  = $tic['tarif'];
				$bill["manifid"]	= $tic["manifid"];
				
				// places num
				$bill["plnum"] = $plnum[$rec['id']]['plname'];
				
				// dernière chose à faire sinon erreur !!
				$bill["orga"]		= array($config['ticket']['seller']['nom']);
				if ( $tic["organisateur"] ) $bill["orga"][] = $tic["organisateur"];
				while ( $tic = $ticket->getRecordNext() )
				if ( $tic["organisateur"] )
					if ( $tic["organisateur"] ) $bill["orga"][] = $tic["organisateur"];
				
				$bill["org"]	= implode(" / ",$bill["orga"]);
				
				// on sort un billet par personne le cas échéant
				if ( !$group )
					$tickets->addToContent($bill);
				else
				{
					$grpbill = $bill;
					$grpbill["nbgroup"] = ++$nbgroup;
				}
			} // if ( $tic = $ticket->getRecordNext() )
			
			$ticket->free();
		
		} // if ( $transac > 0 )
		
	} //  for ( $i = 0 ; $rec = $request->getRecordNext() ; $i++ )
	
	// on sort un billet de groupe le cas échéant
	if ( $group )
		$tickets->addToContent($grpbill);
	
	$tickets->printAll();
		
	$request->free();
	$bd->endTransaction();
	$bd->free();
?>
