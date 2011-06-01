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
	global $bd,$user,$data,$default,$config,$sqlcount,$css,$compta;
	
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
		if ( !$bd->addRecord("facture",array("transaction" => $data["numtransac"], 'accountid' => $user->getId())) )
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
	
	$compta = array();
	$i = 0;
	if ( $rec = $request->getRecord() )
	{
		$compta[$i][]	= $config['ticket']['facture_prefix'].$rec["factureid"];		// numéro de facture
		$compta[$i][]	= $rec["prenom"];			// prenom
		$compta[$i][]	= $rec["nom"];				// nom
		$compta[$i][]	= $rec["orgnom"];			// nom de orga
		$compta[$i][]	= $rec["orgnom"]
				? trim($rec["orgadr"])
				: trim($rec["adresse"]);		// adresse de l'orga
		$compta[$i][] 	= $rec["orgnom"]
				? $rec["orgcp"]
				: $rec["cp"];				// cp de l'orga
		$compta[$i][]	= $rec["orgnom"]
				? $rec["orgville"]
				: $rec["ville"];			// ville de l'orga
		$compta[$i][]	= $rec["orgnom"]
				? $rec["orgpays"]
				: $rec["pays"];				// pays de l'orga
		$compta[$i][]	= $rec["transaction"];			// numéro de BdC
		
		while ( $rec = $request->getRecordNext() )
		{
			$i++;
			$compta[$i][] = $rec["evtnom"];				// titre du spectacle
			$compta[$i][] = date("Y/m/d",strtotime($rec["date"]));	// date
			$compta[$i][] = date("H:i",strtotime($rec["date"]));	// heure
			$compta[$i][] = $rec["sitenom"];				// nom du site
			$compta[$i][] = $rec["siteville"];				// ville du site
			$compta[$i][] = $rec["sitecp"];				// cp du site
			$compta[$i][] = $rec["tarif"];				// tarif
			$compta[$i][] = intval($rec["nb"]);			// nombre
			$compta[$i][] = decimalreplace(floatval($rec["prix"]));	// PU
			$compta[$i][] = decimalreplace(floatval($rec["prix"]) * intval($rec["nb"]));	// total
			$compta[$i][] = decimalreplace($rec["txtva"]);		// taux de TVA en %
		}
	}
	$request->free();
	
	if ( !$config['ticket']['bdc_facture_html_output'] )
	{
	  $csv = new csvExport($compta,isset($_POST["msexcel"]));
	  $csv->printHeaders("facture-".$factureid);
	  echo $csv->createCSV();
	}
  else
  {
    includePage('bdc-facture');
  }
  
  $bd->free();
?>
