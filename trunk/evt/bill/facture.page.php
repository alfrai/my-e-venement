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
	global $bd,$user,$data,$default,$config,$sqlcount;
	
	if ( $user->evtlevel < $config["evt"]["right"]["mod"] )
	{
		$user->addAlert($msg = "Vous n'avez pas un niveau de droits suffisant pour accéder à cette fonctionnalité");
		$nav->redirect($config["website"]["base"]."evt/bill/",$msg);
	}
	
	includeClass("csvExport");
	includeClass("reservations");
	
	// les références du client
	if ( substr($data["client"],0,4) == "prof" )
	{
		$proid		= intval(substr($data["client"],5));
		$query		= " SELECT id FROM personne_properso WHERE fctorgid = ".$proid;
		$request	= new bdRequest($bd,$query);
		$clientid	= intval($request->getRecord("id"));
		$request->free();
	}
	else
	{
		$clientid = intval(substr($data["client"],5));
		$proid = NULL;
	}
	
	// le numéro de facture
	$request = new bdRequest($bd,"SELECT * FROM facture WHERE transaction = '".$data["numtransac"]."'");
	if ( $request->countRecords() <= 0 )
	{
		if ( !$bd->addRecord("facture",array("transaction" => $data["numtransac"])) )
			$user->addAlert("Impossible d'ajouter la facture en base, votre facture doit avoir un numéro erronné.");
		$factureid = $bd->getLastSerial("facture","id");
	}
	else	$factureid = intval($request->getRecord("id"));
	$request->free();
	
	$query	= " SELECT facture.transaction, facture.id AS factureid,
		           tarif.description AS tarif, evt.nom AS evtnom, manif.date,
		           site.nom AS sitenom, site.ville AS siteville, site.cp AS sitecp,
		           personne.*, ticket.nb, getprice(manif.id,tarif.id) AS prix, manif.txtva
		    FROM tickets2print_bytransac('".$data["numtransac"]."') AS ticket,
		    	 manifestation AS manif, site, facture, tarif,
		         evenement AS evt, personne_properso AS personne
		    WHERE personne.id = ".$clientid."
		      AND ticket.printed = true
		      AND ticket.canceled = false
		      AND personne.fctorgid ".($proid ? "= ".$proid : "IS NULL")."
		      AND facture.id = ".$factureid."
		      AND ticket.transaction = facture.transaction
		      AND evt.id = manif.evtid
		      AND ticket.manifid = manif.id
		      AND get_tarifid(manif.id,ticket.tarif) = tarif.id
		      AND manif.siteid = site.id";
	$request = new bdRequest($bd,$query);
	
	$arr = array();
	$i = 0;
	if ( $rec = $request->getRecord() )
	{
		$arr[$i][]	= 'FB'.$rec["factureid"];		// numéro de facture
		$arr[$i][]	= $rec["prenom"];			// prenom
		$arr[$i][]	= $rec["nom"];				// nom
		$arr[$i][]	= $rec["orgnom"];			// nom de orga
		$arr[$i][]	= $rec["orgnom"]
				? trim($rec["orgadr"])
				: trim($rec["adresse"]);		// adresse de l'orga
		$arr[$i][] 	= $rec["orgnom"]
				? $rec["orgcp"]
				: $rec["cp"];				// cp de l'orga
		$arr[$i][]	= $rec["orgnom"]
				? $rec["orgville"]
				: $rec["ville"];			// ville de l'orga
		$arr[$i][]	= $rec["orgnom"]
				? $rec["orgpays"]
				: $rec["pays"];				// pays de l'orga
		$arr[$i][]	= $rec["transaction"];			// numéro de BdC
		
		while ( $rec = $request->getRecordNext() )
		{
			$i++;
			$arr[$i][] = $rec["evtnom"];				// titre du spectacle
			$arr[$i][] = date("Y/m/d",strtotime($rec["date"]));	// date
			$arr[$i][] = date("H:i",strtotime($rec["date"]));	// heure
			$arr[$i][] = $rec["sitenom"];				// nom du site
			$arr[$i][] = $rec["siteville"];				// ville du site
			$arr[$i][] = $rec["sitecp"];				// cp du site
			$arr[$i][] = $rec["tarif"];				// tarif
			$arr[$i][] = intval($rec["nb"]);			// nombre
			$arr[$i][] = decimalreplace(floatval($rec["prix"]));	// PU
			$arr[$i][] = decimalreplace(floatval($rec["prix"]) * intval($rec["nb"]));	// total
			$arr[$i][] = decimalreplace($rec["txtva"]);		// taux de TVA en %
		}
	}
	$request->free();
	
	$csv = new csvExport($arr,isset($_POST["msexcel"]));
	$csv->printHeaders("facture-".$factureid);
	echo $csv->createCSV();
	
	$bd->free();
?>
