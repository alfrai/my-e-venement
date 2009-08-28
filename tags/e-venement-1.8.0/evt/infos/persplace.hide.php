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
	global $sqlcount;
	
	require_once("conf.inc.php");
	includeClass("bdRequest");
	
	$manifid = intval($_GET["id"]);
		
	if ( $_SERVER["PHP_SELF"] != $config["website"]["root"]."evt/infos/persplace.hide.php" )
	{
?>
<div class="trans">
	<h3>Places/Personnes</h3>
	<p>Extrait les personnes avec leurs places préréservées (BdC uniquement) ou réservées confondues.</p>
	<p class="csvext">
		<span>Extraction <a href="evt/infos/persplace.hide.php?id=<?php echo $manifid ?>">standard</a>...</span>
		<span>Extraction <a href="evt/infos/persplace.hide.php?id=<?php echo $manifid ?>&msoffice">compatible Microsoft</a>...</span>
	</p>
</div>
<?php
	}
	else
	{
		$query = " CREATE TEMP TABLE tickets AS
			    SELECT tickets.*
			    FROM tickets2print_bymanif(".$manifid.") AS tickets
			    WHERE tickets.transaction IN (SELECT transaction FROM bdc)
			       OR tickets.printed AND NOT tickets.canceled";
		$request = new bdRequest($bd,$query);
		$request->free();
		
		includeClass("csvExport");
		
		$arr = array();
		$query = " SELECT evt.*, manif.date, manif.jauge, manif.txtva, site.nom AS sitenom, site.ville AS siteville
			   FROM manifestation AS manif,evenement AS evt, site
			   WHERE manif.id = ".$manifid." AND evtid = evt.id AND siteid = site.id";
		$manif = new bdRequest($bd,$query);
		$i = 0;
		
		if ( $rec = $manif->getRecord() )
		{
			$arr[$i] = array();
			$arr[$i][] = $rec["nom"];
			$arr[$i][] = date($config["format"]["date"]." ".$config["format"]["maniftime"],strtotime($rec["date"]));
			$arr[$i][] = $rec["sitenom"]." (".$rec["siteville"].")";
			$arr[$i][] = "Jauge: ".intval($rec["jauge"]);
			$manif->free();
		}
		else
		{
			$user->addAlert("Manifestation introuvable");
			$manif->free();
			exit(1);
		}
		
		$arr[++$i] = array();
		$arr[++$i] = array();
		$arr[$i][] = "Transaction";
		$arr[$i][] = "Nom";
		$arr[$i][] = "Prenom";
		$arr[$i][] = "Adresse";
		$arr[$i][] = "CP";
		$arr[$i][] = "Ville";
		$arr[$i][] = "Pays";
		$arr[$i][] = "Organisme";
		$arr[$i][] = "Fonction";
		
		$tickets = array();
		$query = " SELECT DISTINCT tarif.id, tarif.key AS tarif, reduc
			   FROM tarif, reservation_pre AS pre
			   WHERE tarif.id = pre.tarifid
			     AND manifid = ".$manifid."
			   ORDER BY tarif.key";
		$request = new bdRequest($bd,$query);
		while ( $rec = $request->getRecordNext() )
		{
			$arr[$i][] = $rec["tarif"].' '.(intval($rec["reduc"]) < 10 ? "0" : "").intval($rec["reduc"]);
			$tickets[$rec["tarif"].intval($rec["reduc"])] = count($arr[$i]) - 1;
		}
		$request->free();
		
		$query	= " SELECT tickets.tarif, tickets.reduc, trans.id AS transaction, tickets.nb,
			           pers.nom, pers.prenom, pers.adresse, pers.cp, pers.ville, pers.pays,
			           pers.orgnom, pers.orgadr, pers.orgcp, pers.orgville, pers.orgpays, pers.fcttype, pers.fctdesc
			    FROM tickets, transaction AS trans, personne_properso AS pers
			    WHERE transaction = trans.id
			      AND pers.id = trans.personneid
			      AND ( pers.fctorgid = trans.fctorgid OR pers.fctorgid IS NULL AND trans.fctorgid IS NULL )
			    ORDER BY pers.nom, pers.prenom, pers.orgnom";
		$persplace = new bdRequest($bd,$query);
		$transaction = 0;
		
		while ( $rec = $persplace->getRecordNext() )
		{
			if ( $transaction != $rec["transaction"] )
			{
				$arr[++$i] = array();
				$arr[$i][] = $rec["transaction"];
				$arr[$i][] = $rec["nom"] ? $rec["nom"] : '-';
				$arr[$i][] = $rec["prenom"];
				$arr[$i][] = $rec["orgnom"] ? $rec["orgadr"] : $rec["adresse"];
				$arr[$i][] = $rec["orgnom"] ? $rec["orgcp"] : $rec["cp"];
				$arr[$i][] = $rec["orgnom"] ? $rec["orgville"] : $rec["ville"];
				$arr[$i][] = $rec["orgnom"] ? $rec["orgpays"] : $rec["pays"];
				$arr[$i][] = $rec["orgnom"];
				$arr[$i][] = $rec["fctdesc"] ? $rec["fctdesc"] : $rec["fcttype"];
				foreach ( $tickets as $value )
					$arr[$i][] = 0;
				$transaction = $rec["transaction"];
			}
			$arr[$i][$tickets[$rec["tarif"].$rec["reduc"]]] = $rec["nb"];
		}
		$persplace->free();
		
		$csv = new csvExport($arr,isset($_GET["msoffice"]));
		$csv->printHeaders("bilan-".$manifid."-persplace-".date("Ymd"));
		echo $csv->createCSV();
		
		$bd->free();
	}
?>
