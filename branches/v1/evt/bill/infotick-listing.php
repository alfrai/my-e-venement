<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    beta-libs is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with beta-libs; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	require("conf.inc.php");
	$class .= "dematerialized";
	
	if ( !$config["ticket"]["dematerialized"] )
	{
		$user->addAlert("Billets dématérialisés indisponibles");
		$nav->redirect($config["website"]["base"].'evt/bill');
	}
	
	// manifestation.id
	$manifid = intval($_GET["manif"]);
	if ( $manifid <= 0 )
	{
		$user->addAlert("Manifestation inexistante ou non spécifiée.");
		$nav->redirect($config["website"]["base"].'evt/bill/infotick.php');
	}
	
	includeClass("csvExport");
	
	$query	= " SELECT manif.id AS manifid, evt.nom AS evtnom, site.nom AS sitenom, manif.date,
		           personne.nom, personne.prenom, personne.adresse, personne.ville, personne.cp, personne.email,
		           transaction, preresa.plnum, count(*) AS nb
		    FROM reservation_pre AS preresa, reservation_cur AS resa, personne, transaction,
		         manifestation AS manif, evenement AS evt, site
		    WHERE resa.resa_preid = preresa.id
		      AND transaction.personneid = personne.id
		      AND preresa.transaction = transaction.id
		      AND NOT canceled
		      AND NOT annul
		      AND dematerialized
		      AND NOT dematerialized_passed
		      AND manif.id = manifid
		      AND evt.id = evtid
		      AND site.id = siteid
		      AND manifid = ".$manifid."
		    GROUP BY manif.id, evt.nom, site.nom, manif.date,
		             personne.nom, personne.prenom, personne.adresse, personne.cp, personne.ville, personne.email,
		             transaction, preresa.plnum";
	$request = new bdRequest($bd,$query);
	
	$arr = array();
	
	$arr[] = array();
	$arr[count($arr)-1][] = '#'.$request->getRecord("manifid");
	if ( $request->getRecord() )
	{
		$arr[count($arr)-1][] = $request->getRecord("evtnom");
		$arr[count($arr)-1][] = $request->getRecord("sitenom");
		$arr[count($arr)-1][] = date($config["format"]["date"]." ".$config["format"]["maniftime"],strtotime($request->getRecord("date")));
	
		$arr[] = array();
	}
	
	$transaction = 0;
	while ( $rec = $request->getRecordNext() )
	{
		if ( $transaction != intval($rec["transaction"]) )
		{
			if ( $transaction != 0 )
				$arr[count($arr)-1][] = implode(", ",$tmp);
			$transaction = intval($rec["transaction"]);
			$tmp = array();
			$arr[] = array();
			$arr[count($arr)-1][] = $rec["nom"];
			$arr[count($arr)-1][] = $rec["prenom"];
			$arr[count($arr)-1][] = $rec["adresse"];
			$arr[count($arr)-1][] = $rec["cp"];
			$arr[count($arr)-1][] = $rec["ville"];
			$arr[count($arr)-1][] = $rec["email"];
			$arr[count($arr)-1][] = '#'.intval($rec["transaction"]);
		}
		$tmp[] = (intval($rec["nb"]) > 1 ? intval($rec["nb"]) : "")." pl. ".(is_null($rec["plnum"]) ? "libre(s)" : "n°".$rec["plnum"]);
	}
	if ( $transaction != 0 )
		$arr[count($arr)-1][] = implode(", ",$tmp);
	
	$csv = new csvExport($arr);
	$csv->printHeaders();
	echo $csv->createCSV();
	
	$request->free();
	$bd->free();	
?>
