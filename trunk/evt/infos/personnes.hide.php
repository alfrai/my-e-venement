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
	
	function printTrans($rec)
	{
		echo '<li '.($rec['nb'] < 0 || $rec['annulation'] == 't' ? 'class="annul"' : '').'>';
		echo '<span class="trans">';
		echo '#<a href="evt/bill/'.($rec["contingeant"] == 't' ? 'depot.php' : 'billing.php').'?t='.intval($rec["transaction"]).'">'.intval($rec["transaction"]).'</a>';
		echo '</span>: ';
		if ( $rec['translinked'] ) echo '<span class="translinked">#'.intval($rec['translinked']).'</span>';
		echo '<span class="contingeant">'.($rec["contingeant"] == 't' && $rec["depot"] == 'f' ? '(contingent)' : ($rec["depot"] == 't' ? '(depot)' : '')).'</span> ';
		echo '<span class="personne"><a '.( $rec["id"] ? 'href="ann/fiche.php?id='.$rec["id"].'&view"' : '').'>'.htmlsecure($rec["nom"]." ".$rec["prenom"]).'</a></span> ';
		if ( !is_null($rec["orgid"]) )
		{
			echo '<span class="organisme">(<a href="org/fiche.php?id='.$rec["orgid"].'&view">'.$rec["orgnom"].'</a>';
			if ( $rec["fctdesc"] || $rec["fcttype"])
				echo ' - '.htmlsecure($rec["fctdesc"] ? $rec["fctdesc"] : $rec["fcttype"]);
			echo ')</span> ';
		}
		echo ' → <span class="places">'.intval($rec["nb"]).' pl.</span>';
		if ( $rec['numfacture'] ) echo ' - <span class="facture">#'.intval($rec["numfacture"]).' (facture)</span>';
		echo '</li>';
	}
	
	if ( $_SERVER["PHP_SELF"] == $config["website"]["root"]."evt/infos/personnes.hide.php" || $more )
	{
		$query = " CREATE TEMP TABLE tickets AS
			    SELECT *
			    FROM tickets2print_bymanif(".$manifid.");
			   CREATE TEMP TABLE tmptab AS
			    SELECT sum(nb) AS nb, transac.id IN (SELECT transaction FROM contingeant) AS contingeant,
			           transac.id IN (SELECT transaction FROM masstickets) AS depot,
			           printed AND NOT canceled AS resa,
			           transac.id IN (SELECT transaction FROM preselled) AS preresa,
			           transac.id AS transaction, personne.id, personne.nom, personne.prenom, personne.adresse, personne.cp, personne.ville, personne.pays,
			           personne.orgid, personne.fctorgid, personne.orgnom, personne.fcttype, personne.fctdesc, personne.orgadr, personne.orgcp, personne.orgville, personne.orgpays,
			           facture.id AS numfacture, transac.translinked,
			           (SELECT count(*) > 0
			            FROM transaction t, reservation_pre p, reservation_cur c
			            WHERE t.translinked = transac.id
			              AND t.id = p.transaction
			              AND p.id = c.resa_preid
			              AND NOT c.canceled
			              AND p.annul) AS annulation
			    FROM tickets AS resa, personne_properso AS personne, transaction AS transac
			    LEFT JOIN facture ON (transac.id = facture.transaction)
			    WHERE (    personne.fctorgid = transac.fctorgid
			            OR personne.fctorgid IS NULL AND transac.fctorgid IS NULL
			           AND ( transac.personneid = personne.id OR personne.id IS NULL AND transac.personneid IS NULL ))
			      AND transac.id = resa.transaction
			      ".($_GET['spaces'] != 'all' ? "AND transac.spaceid ".($user->evtspace ? '= '.$user->evtspace : 'IS NULL') : '')."
			    GROUP BY personne.id, personne.nom, personne.prenom, personne.orgid, personne.orgnom, contingeant, resa, preresa, transac.id, fcttype, fctdesc, personne.fctorgid,
			    	     personne.orgadr, personne.orgcp, personne.orgville, personne.orgpays, personne.adresse, personne.cp, personne.ville, personne.pays, facture.id, translinked
			    ORDER BY translinked DESC, annulation, nom, prenom";
		$request = new bdRequest($bd,$query);
		$request->free();
	}
		
	if ( $_SERVER["PHP_SELF"] != $config["website"]["root"]."evt/infos/personnes.hide.php" )
	{
?>
<div class="trans">
	<h3>Personnes</h3>
	<?php
		if ( $more ) {
		
		echo '<h4 class="demandes">Demandes</h4>';
		$query = " SELECT *
			   FROM tmptab
			   WHERE NOT preresa AND NOT resa";
		$request = new bdRequest($bd,$query);
		echo '<ul>';
		while ( $rec = $request->getRecordNext() )
			printTrans($rec);
		echo '</ul>';
		$request->free();
		
		echo '<h4 class="preresas">Pré-réservations</h4>';
		$query = " SELECT *
			   FROM tmptab
			   WHERE preresa AND NOT resa";
		$request = new bdRequest($bd,$query);
		echo '<ul>';
		while ( $rec = $request->getRecordNext() )
			printTrans($rec);
		echo '</ul>';
		$request->free();
		
		echo '<h4 class="resas">Réservations</h4>';
		$query = " SELECT *
			   FROM tmptab
			   WHERE resa";
		$request = new bdRequest($bd,$query);
		echo '<ul>';
		while ( $rec = $request->getRecordNext() )
			printTrans($rec);
		echo '</ul>';
		if ( $request->countRecords() > 0 )
		$request->free();
		
		
		} // if ( $more )
	?>
	<p class="csvext">
		<span><a href="evt/infos/group.hide.php?manifid=<?php echo $manifid ?>">Export</a> vers un groupe personnel</span>
		<span>Extraction <a href="evt/infos/personnes.hide.php?id=<?php echo $manifid ?>&spaces=<?php echo htmlsecure($_GET['spaces']) ?>">standard</a>...</span>
		<span>Extraction <a href="evt/infos/personnes.hide.php?id=<?php echo $manifid ?>&spaces=<?php echo htmlsecure($_GET['spaces']) ?>&msoffice">compatible Microsoft</a>...</span>
	</p>
</div>
<?php
	}
	else
	{
		includeClass("csvExport");
		
		$arr = array();
		$i = 0;
		
		/*
		$query = " SELECT evt.*, manif.date, manif.jauge, manif.txtva, site.nom AS sitenom, site.ville AS siteville
			   FROM manifestation AS manif,evenement AS evt, site
			   WHERE manif.id = ".$manifid." AND evtid = evt.id AND siteid = site.id";
	  */
	  
	  require 'query.hide.php';
		$manif = new bdRequest($bd,$query);
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
		$arr[$i][] = "Contingeant / Depot";
		$arr[$i][] = "Places demandees";
		$arr[$i][] = "Places pre-reservees";
		$arr[$i][] = "Places reservees";
		$arr[$i][] = "Facture";
		
		$query	= " SELECT *
			    FROM tmptab";
		$request = new bdRequest($bd,$query);
		$transaction = 0;
		while ( $rec = $request->getRecordNext() )
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
				$arr[$i][] = $rec["contingeant"] == 't' ? '1' : '0';
				$arr[$i][] = 0;	// 6. demandes
				$arr[$i][] = 0;	// 7. preresas
				$arr[$i][] = 0;	// 8. resas
				$arr[$i][] = $rec["numfacture"];// 9. facture
				$transaction = $rec["transaction"];
			}
			if ( $rec["preresa"] != "t" && $rec["resa"] != "t" )
				$arr[$i][count($arr[$i])-4] = intval($rec["nb"]);
			elseif ( $rec["preresa"] == "t" && $rec["resa"] != "t" )
				$arr[$i][count($arr[$i])-3] = intval($rec["nb"]);
			elseif ( $rec["resa"] == "t" )
				$arr[$i][count($arr[$i])-2] = intval($rec["nb"]);
		}
		$request->free();
		
		$csv = new csvExport($arr,isset($_GET["msoffice"]));
		$csv->printHeaders("bilan-".$manifid."-personnes-".date("Ymd"));
		echo $csv->createCSV();
		
		$bd->free();
	}
?>
