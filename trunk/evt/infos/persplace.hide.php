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
		<span><input type="checkbox" name="all" value="yes" onchange="javascript: $(this).parent().parent().find('a').attr('href',this.checked ? 'evt/infos/persplace.hide.php?id=<?php echo $manifid ?>&all' : 'evt/infos/persplace.hide.php?id=<?php echo $manifid ?>');" />&nbsp;Extraire même les demandes...</span>
		<span>Extraction <a href="evt/infos/persplace.hide.php?id=<?php echo $manifid ?>">standard</a>...</span>
		<span>Extraction <a href="evt/infos/persplace.hide.php?id=<?php echo $manifid ?>" style="cursor:pointer;" onclick="javascript: this.href += '&msoffice';">compatible Microsoft</a>...</span>
	</p>
</div>
<?php
	}
	else
	{
		$query = " CREATE TEMP TABLE tickets AS
			    SELECT tickets.*
			    FROM tickets2print_bymanif(".$manifid.") AS tickets";
		if ( !isset($_GET['all']) )
		{
		  $query .= '
			    WHERE tickets.transaction IN (SELECT transaction FROM bdc)
			       OR tickets.printed AND NOT tickets.canceled';
		}
		/*
		$query  = ' CREATE TEMP TABLE tickets AS
		            SELECT p.transaction, p.manifid, sum((NOT p.annul)::integer*2-1) AS nb,
		                   t.key AS tarif, c.id IS NOT NULL AS printed, c.canceled, t.prix, mt.prix AS prixspec
		            FROM tarif t, reservation_pre p
		            LEFT JOIN reservation_cur c ON c.resa_preid = p.id AND NOT canceled
		            LEFT JOIN manifestation_tarifs mt ON mt.tarifid = p.tarifid AND mt.manifestationid = p.manifid
		            LEFT JOIN personne_properso ppp ON ppp.id = p.personneid
		            WHERE manifid = '.$manifid.'
		              AND t.id = p.tarifid
		            GROUP BY p.transaction, p.manifid, t.key, printed, c.canceled, t.prix, mt.prix';
		*/
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
		
		$arr[$i][] = 'Imprimés';
		$arr[$i][] = 'Reste dû';
		
		$query	= " SELECT tickets.tarif, tickets.reduc, trans.id AS transaction, tickets.nb, tickets.printed, tickets.prix, tickets.prixspec,
			           pers.nom, pers.prenom, pers.adresse, pers.cp, pers.ville, pers.pays,
			           pers.orgnom, pers.orgadr, pers.orgcp, pers.orgville, pers.orgpays, pers.fcttype, pers.fctdesc,
			           (SELECT p.prix FROM paid p WHERE p.transaction = trans.id) AS paid
			          FROM tickets, transaction AS trans
			          LEFT JOIN personne_properso AS pers ON pers.id = trans.personneid AND ( pers.fctorgid = trans.fctorgid OR pers.fctorgid IS NULL AND trans.fctorgid IS NULL )
			          WHERE transaction = trans.id
			          ORDER BY pers.nom, pers.prenom, pers.orgnom, trans.id";
		
		$persplace = new bdRequest($bd,$query);
		$transaction = 0;
		
		while ( $rec = $persplace->getRecordNext() )
		{
			if ( $transaction != $rec["transaction"] )
			{
			  if ( $transaction != 0 )
			  {
			    $arr[$i][] = $printed;
			    $arr[$i][] = str_replace('.',',',(string)$topay - $paid);
			  }
			  
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
				$printed = $topay = 0;
			}
			$printed += intval($rec['nb'])*($rec['printed'] == 't' ? 1 : 0);
			$arr[$i][$tickets[$rec["tarif"].$rec["reduc"]]] = $rec["nb"];
			$topay += intval($rec['nb'])*floatval($rec['prixspec'] ? $rec['prixspec'] : $rec['prix']);
			$paid = floatval($rec['paid']);
		}
		if ( $i > 0 )
		{
		  $arr[$i][] = $printed;
      $arr[$i][] = str_replace('.',',',(string)$topay - $paid);
		}
		$persplace->free();
		
		$csv = new csvExport($arr,isset($_GET["msoffice"]));
		$csv->printHeaders("bilan-".$manifid."-persplace-".date("Ymd"));
		echo $csv->createCSV();
		
		$bd->free();
	}
?>
