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
	if ( is_array($_GET["plnum"]) && $config["ticket"]["placement"] )
	{
		// pas deux numéros de place identiques
		$test = array_count_values($_GET["plnum"]);
		foreach ( $test as $key => $value )
		if ( $value > 1 || intval($key)."" != $key )
		{
			$user->addAlert("Impossible de lancer l'impression : vous avez affecté deux billets à la même place.");
			$go = false;
			break;
		}
		
		if ( $go )
		foreach ( $_GET["plnum"] as $value )
		{
			// numéro encore non réservé
			$query  = " SELECT
				     (SELECT count(*) > 0
				      FROM manifestation_plnum
				      WHERE manifestationid = ".$manifid."
				        AND plnum = ".intval($value).")
				    AND
				     (SELECT SUM((NOT annul)::integer*2-1) = 0 ".($resa["nb"] < 0 ? "+ 1" : "")."
				      FROM (SELECT 1 as num, annul FROM reservation_pre WHERE manifid = ".intval($_GET["manifid"])." AND plnum = ".intval($value)." AND NOT transaction = '".pg_escape_string($transac)."'
				            UNION
				            SELECT 2 AS num, true AS annul
				            UNION
				            SELECT 3 AS num, false AS annul) AS tmp)
				    AS ok";
			$request = new bdRequest($bd,$query);
			if ( $request->getRecord("ok") != 't' )
			{
				$user->addAlert("Impossible de lancer l'impression : la place indiquée est déjà réservée dans une autre transaction.");
				$go = false;
			}
			$request->free();
		}
		
		// on n'imprime rien si il y a une erreur
		if ( !$go )
		{
			$url = parse_url($_SERVER["HTTP_REFERER"]);
			$nav->redirect($url["scheme"]."://".$url["host"].$url["port"].$url["path"]."?t=".urlencode($transac)."&s=3");
		}
		
		$plnum = array();
		foreach ( $_GET["plnum"] as $value )
		if ( intval($value)."" == $value && intval($value) > 0 )
			$plnum[] = intval($value);
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
	$bd->updateRecords("reservation_cur",
			   "    reduc = ".$resa["reduc"]."
			    AND pre.id = resa_preid
			    AND tarifid = ".$tarifid."
			    AND manifid = ".$manifid."
			    AND transaction = '".pg_escape_string($transac)."'",
			   array("canceled" => "t"),
			   "reservation_pre AS pre");
	
	// la fonction qui "sort" le billet graphiquement
	function printBill($bill,$group = false)
	{
		global $config;
		
		$time = strtotime($bill["date"]);
		$date["big"]  = strtolower($config["dates"]["DOTW"][date("w",$time)]).date(" d ",$time);
		$date["big"] .= strtolower($config["dates"]["MOTY"][intval(date("n",$time))-1]);
		$date["big"] .= date(" Y / H\hi",$time);
		
		$date["ltl"]  = date("d ",$time);
		$date["ltl"] .= strtolower($config["dates"]["moty"][intval(date("n",$time))-1]);
		$date["ltl"] .= date(" Y / H\hi",$time);
	?>
<div class="page">
	<div class="ticket">
                <div class="left">
                	<?php if ( isset($bill["info"]) ) echo '<p class="info '.$bill["info"].'">'.$bill["info"].'</p>'; ?>
                	<p class="manifid">#<?php echo htmlsecure($bill["manifid"]); ?></p>
                	<p class="metaevt"><?php echo htmlsecure($bill["metaevt"]); ?></p>
                	<p class="dateheure"><?php echo htmlsecure($date["big"]); ?></p>
                	<p class="lieuprix"><span class="lieu"><?php echo htmlsecure($bill["sitenom"]); ?></span> / <span class="prix"><?php echo htmlsecure($bill["prix"] ? $bill["prix"]."€" : "Exonéré"); ?></span></p>
                	<p class="titre"><?php echo htmlsecure(strlen($buf = $bill["evtnom"]) > 30 ? substr($buf,0,30).'...' : $buf);?></p>
                	<p class="cie"><?php echo htmlsecure($bill["createurs"]); ?></p>
                	<p class="org">Org: <?php echo htmlsecure(strlen($bill["org"]) > 60 ? substr($bill["org"],0,60)." ..." : $bill["org"]); ?></p>
                	<p class="placement"><?php echo htmlsecure($bill["plnum"] ? "Place n°".$bill["plnum"] : "Placement libre ".($group ? ' - x'.$bill["nbgroup"] : '')); ?></p>
                	<p class="operation"><span class="date"><?php echo htmlsecure(date("d/m/Y H:i")); ?></span> / <span class="num">#<?php echo htmlsecure($bill["ticketid"]); ?></span>-<span class="operateur">4</span></p>
                	<p class="mentions">À conserver</p>
                </div>
                <div class="right">
                	<?php if ( isset($bill["info"]) ) echo '<p class="info '.$bill["info"].'">'.$bill["info"].'</p>'; ?>
                 	<p class="manifid">#<?php echo htmlsecure($bill["manifid"]); ?></p>
                	<p class="metaevt"><?php echo htmlsecure($bill["metaevt"]); ?></p>
                	<p class="dateheure"><?php echo htmlsecure($date["ltl"]); ?></p>
                	<p class="lieuprix"><span class="lieu"><?php echo htmlsecure(strlen($buf = $bill["sitenom"]) > 14 ? substr($buf,0,11).'...' : $buf); ?></span> / <span class="prix"><?php echo htmlsecure($bill["prix"]); ?>€</span></p>
                	<p class="titre"><?php echo htmlsecure(strlen($buf = $bill["evtnom"]) > 18 ? substr($buf,0,15).'...' : $buf);?></p>
                	<p class="cie"><?php echo htmlsecure(strlen($buf = $bill["createurs"]) > 20 ? substr($buf,0,17).'...' : $buf); ?></p>
                	<p class="org">Org: <?php echo htmlsecure($bill["orga"][0]); ?></p>
                	<p class="placement"><?php echo htmlsecure($bill["plnum"] ? "Place n°".$bill["plnum"] : "Placement libre ".($group ? ' - x'.$bill["nbgroup"] : '')); ?></p>
                	<p class="operation"><span class="date"><?php echo htmlsecure(date("d/m/Y H:i")); ?></span> / <span class="num">#<?php echo htmlsecure($bill["ticketid"]); ?></span>-<span class="operateur">4</span></p>
                	<p class="mentions">Contrôle</p>
                </div>
        </div>
</div>
<?php	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr-FR">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>e-venement : impression de tickets</title>
  <style>
  	body {
  		font-family: din, verdana, arial;
  		font-size: 10pt;
  		font-weight: bold;
  		margin: 0; padding: 0;
  	}
  	.page { clear: both; page-break-after: always; }
  	.ticket {
  		width: 150mm;
  		height: 58mm;
  	}
  	.left, .right {
  		overflow: hidden;
  		border: 1px solid none;
  	}
  	.left {
  		border-color: blue;
  		width: 108.8mm;
  		height: 58mm;
  		float: left;
  		text-align: right;
  		padding-right: 1.2mm;
  	}
  	.right {
  		border-color: green;
  		width: 39.2mm;
  		height: 58mm;
  		padding-left: 0.8mm;
  	}
  	
  	p {
  		margin: 0;
  		padding: 0;
  		overflow: hidden;
  		white-space: nowrap;
  	}
  	
  	.info {
  		border: 1px solid black;
  		float: right;
  		font-size: 8pt;
  		font-weight: normal;
		padding: 0 8px 2px 8px;
  		margin-top: 2px;
  	}
  	.right .info { float: left; }
  	.info.annulation {
  		background-color: black;
  		color: white;
  	}
  	
  	.left .manifid { text-align: left; margin-left: 2mm; }
  	.metaevt { font-size: 8pt; margin-top: 2.5mm; }
  	.lieuprix, .dateheure { font-size: 11pt; }
  	.dateheure { margin-top: 1.5mm; }
  	.lieuprix { margin-top: 0.5mm; }
  	.titre { font-size: 21px; margin-top: 2mm; }
  	.cie { font-size: 12pt; margin-top: 1mm; }
  	.placement, .mentions, .org { font-size: 8pt; }
  	.placement, .mentions, .operation { font-weight: normal; }
  	.org { margin-top: 2mm; }
  	.placement { margin-top: 1mm; }
  	.operation { font-size: 6pt; margin-top: 1.5mm; }
  	.mentions { margin-top: 1mm; }
  	
  	.right .manifid { text-align: right; margin-left: 5mm; }
  	.right .metaevt { font-size: 7pt; padding-top: 0.5pt; }
  	.right .lieuprix, .right .dateheure { font-size: 8pt; padding-top: 3pt; }
  	.right .titre { font-size: 11pt; padding-top: 6.5pt; }
  	.right .cie, .right .org { font-size: 8pt; }
  	.right .cie { padding-top: 5pt; }
  </style>
</head>
<body onload="javascript: /*print(); close();*/">
<?php	
	// récup des pré_resa à imprimer (et donc à confirmer)
	$query	= " SELECT resa.*, '".pg_escape_string($resa["tarif"])."' AS key, resa.id IN (SELECT resa_preid FROM reservation_cur WHERE canceled = true) AS duplicata
		    FROM reservation_pre AS resa
		    WHERE transaction = '".pg_escape_string($transac)."'
		      AND resa.id NOT IN ( SELECT resa_preid FROM reservation_cur WHERE canceled = false )
		      AND tarifid = ".$tarifid."
		      AND reduc = ".$resa["reduc"]."
		      AND manifid = ".$manifid;
	$request = new bdRequest($bd,$query);
	
	$ok = true;
	$ticketid = 0;
	for ( $i = 0 ; $rec = $request->getRecordNext() ; $i++ )
	{
		$ticketid = intval($rec["transaction"]);
		$arr = array();
		$arr["resa_preid"]	= $resa_preid = intval($rec["id"]);
		$arr["accountid"]	= $user->getId();
		$ok = $ok && $bd->addRecord("reservation_cur",$arr);
		
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
				           getprice(manif.id,".$tarifid.") AS prix, NULL AS organisateur, manif.id AS manifid
				    FROM billeterie.manifestation AS manif, evenement AS evt,
				         site, reservation_pre AS preresa, reservation_cur AS resa
				    WHERE manif.id = ".$manifid."
				      AND evt.id = manif.evtid
				      AND site.id = manif.siteid
				      AND preresa.manifid = manif.id
				      AND resa.resa_preid = preresa.id
				      AND manif.id NOT IN (SELECT manifid FROM manif_organisation))
				   UNION
				   (SELECT evt.petitnom AS evtnom, evt.nom AS evtbignom, evt.typedesc, evt.metaevt,
				   	   (SELECT libelle FROM evt_categorie WHERE id = evt.categorie) AS catdesc,
				           (SELECT nom FROM organisme WHERE evt.organisme1 = id) AS organisme1,
				           (SELECT nom FROM organisme WHERE evt.organisme2 = id) AS organisme2,
				           (SELECT nom FROM organisme WHERE evt.organisme3 = id) AS organisme3,
				            ".$ticketid." AS ticketid, manif.date, site.nom AS sitenom, site.ville AS siteville,
				       	    getprice(manif.id,".$tarifid.") AS prix, organisme.nom AS organisateur, manif.id AS manifid
				    FROM billeterie.manifestation AS manif, evenement AS evt, organisme, manif_organisation AS orga,
				         site, reservation_pre AS preresa, reservation_cur AS resa
				    WHERE manif.id = ".$manifid."
				      AND evt.id = manif.evtid
				      AND site.id = manif.siteid
				      AND orga.manifid = manif.id
				      AND preresa.manifid = manif.id
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
				$bill["createurs"]	= strlen($buf = implode(" / ",$arr)) > 40
							? substr($buf,0,40)." ..."
							: $buf;
				$bill["evtnom"]		= $tic["evtnom"] ? $tic["evtnom"] : $tic["evtbignom"];
				$bill["metaevt"]	= $tic["metaevt"];
				$bill["type"]		= $tic["typedesc"] ? $tic["typedesc"] : $tic["catdesc"];
				$bill["num"]		= intval($tic["ticketid"]);
				$bill["operateur"]	= $user->getId();
				$bill["date"]		= $tic["date"];
				$bill["sitenom"]	= $tic["sitenom"];
				$bill["siteville"]	= $tic["siteville"];
				$bill["prix"]		= $tic["prix"]*(1-floatval($resa["reduc"])/100);
				$bill["orga"]		= intval($tic["manifid"]);
				$bill["manifid"]	= $tic["manifid"];
				
				$bill["orga"]		= array();
				if ( $tic["organisateur"] ) $bill["orga"][] = $tic["organisateur"];
				while ( $tic = $ticket->getRecordNext() )
				if ( $tic["organisateur"] )
					if ( $tic["organisateur"] ) $bill["orga"][] = $tic["organisateur"];
				$bill["orga"][] = "Très Tôt Théatre";
				
				$bill["org"]	= implode(" / ",$bill["orga"]);
				
				// places num
				$bill["plnum"] = $plnum[$i];
				
				// numéro d'opération
				$bill["ticketid"] = $ticketid;
				
				// mise à jour du record
				$ok = $ok && $bd->updateRecordsSimple("reservation_pre",array("id" => $resa_preid),array("plnum" => $plnum[$i]));
				
				// on sort un billet par personne le cas échéant
				if ( !$group )
					printBill($bill);
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
		printBill($grpbill,$group);
		
	$request->free();
	$bd->endTransaction();
	$bd->free();
?>
</body>
</html>
