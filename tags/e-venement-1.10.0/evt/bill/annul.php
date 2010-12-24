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
	includeClass("bdRequest");
	includeClass("reservations");
	includeLib("bill");
	includeLib("ttt");
	
	if ( $user->evtlevel <= $config["evt"]["right"]["view"] )
	{
		$user->addAlert($msg = "Vous n'avez pas un niveau de droits suffisant pour accéder à cette fonctionnalité");
		$nav->redirect($config["website"]["base"]."evt/bill/",$msg);
	}
	
	global $sqlcount;
	$class .= " annul evt";
	$css[]	= "evt/styles/colors.css.php";
	$subtitle = "Annulation de billets";
	$oldtransac = isset($_POST["pretransac"]) ? $_POST["pretransac"] : $_GET["pretransac"];
	if ( $_GET["transac"] ) $transac = $_GET["transac"];
	$stage = $oldtransac ? 2 : 1;
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	if ( $stage > 1 )
	{
		$resa = preg_tarif($_GET["resa"]);
		
		if ( !isset($transac) )
		{
			// enregistrement de la nouvelle transaction
			$arr = array();
			$arr["accountid"] = $user->getId();
			$arr['spaceid']   = $user->evtspace ? $user->evtspace : NULL;
			
			$query = " SELECT *
			           FROM transaction
			           WHERE id = '".pg_escape_string($oldtransac)."'
			             ".($user->evtspace ? "AND spaceid = ".$user->evtspace : '');
			$request = new bdRequest($bd,$query);
			$rec = $request->getRecord();
			$arr["personneid"] = $rec["personneid"];
			$arr["fctorgid"] = $rec["fctorgid"];
			$arr["translinked"] = $oldtransac;
			
			if ( $request->countRecords() > 0 && !$bd->addRecord("transaction",$arr) )
			{
				$user->addAlert("Impossible de créer la transaction, veuillez contacter votre administrateur.");
				$bd->free();
				$nav->redirect("evt/bill/annul.php","Erreur lors de la création de la transaction.");
			}
			$transac = $bd->getLastSerial("transaction","id");
			$request->free();
 		}
		
		// enregistrement des demandes d'annulation
		if ( $resa != preg_tarif(NULL) && ($manifid = intval($_GET["manif"])) >= 0 )
		{
			$reservation = new reservations($bd,$user,$transac);
			$reservation->addPreReservation($manifid,$resa);
		}
		
		$select  = ' pp.id, pp.nom, pp.prenom, pp.titre, pp.orgnom, pp.fcttype, pp.fctdesc,
		             sum((NOT annul)::integer*2-1) AS nb, tarif.key AS tarif, p.reduc,
                 evt.nom AS evtnom, evt.id AS evtid, manif.id AS manifid, site.nom AS sitenom, manif.date, site.ville, site.cp AS manifcp, colors.libelle AS colorname';
		$groupby = ' pp.id, pp.nom, pp.prenom, pp.titre, pp.orgnom, pp.fcttype, pp.fctdesc,
		             tarif.key, p.reduc,
		             evt.nom, evt.id, manif.id, site.nom, manif.date, site.ville, site.cp, colors.libelle';
		$from    = ' manifestation manif LEFT JOIN colors ON colors.id = manif.colorid,
		             evenement evt, site, tarif, reservation_pre p, reservation_cur c,
		             transaction t LEFT JOIN personne_properso pp ON pp.id = t.personneid AND (pp.fctorgid = t.fctorgid OR pp.fctorgid IS NULL AND t.fctorgid IS NULL)';
    $where   = "     evt.id = manif.evtid
	               AND manif.id = p.manifid
	               AND manif.siteid = site.id
	               AND tarif.id = p.tarifid
	               AND p.transaction = t.id
	               AND c.resa_preid = p.id
	               AND NOT c.canceled
	               AND (t.id = '".pg_escape_string($oldtransac)."' OR t.translinked = '".pg_escape_string($oldtransac)."')";
	  $orderby = " key, nb, reduc";
		$query = " SELECT DISTINCT $select
			         FROM $from
	             WHERE $where
	             GROUP BY $groupby
	             ORDER BY $orderby";
		$request = new bdRequest($bd,$query);
		$rec = $request->getRecord();
		
		if ( $request->countRecords() <= 0 )
		{
			$stage = 1;
			$request->free();
		}
		else if ( !isset($transac) )
		{
			$arr = array();
			$arr["accountid"]	= $user->getId();
			$arr['spaceid']   = $user->evtspace ? $user->evtspace : NULL;
			$arr["personneid"]	= intval($rec["id"]);
			$arr["fctorgid"]	= $rec["fctorgid"] ? intval($rec["fctorgid"]) : NULL;
			if ( $bd->addRecord("transaction",$arr) )
				$transac = $bd->getLastSerial("transaction","id");
			else	$stage = 1;
		}
	}
	
	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php") ?>
<div class="body">
<h2><?php echo $subtitle ?></h2>
<?php includePage("grp-stages"); ?>
<?php if ( $stage < 2 ) { ?>
<form name="formu" action="evt/bill/annul.php" method="post" class="search resa">
	<p>Numéro de transaction&nbsp;: #<input type="text" name="pretransac" id="focus" value="<?php echo htmlsecure($name_start) ?>" /></p>
<?php
	}
	else
	{
		$data["client"] = $rec["orgid"] ? "prof_".intval($rec["fctorgid"]) : "pers_".intval($rec["id"]);
		$data["numtransac"] = $transac;
?>
<form name="formu" action="evt/bill/billing.php" method="post" class="print resa">
	<p class="transaction">Spectateur: 
		<span class="titre"><?php echo htmlsecure($rec["titre"]) ?></span>
		<a href="ann/fiche.php?id=<?php echo intval($rec["id"]) ?>&view"><span class="prenom"><?php echo htmlsecure($rec["prenom"]) ?></span>
			<span class="nom"><?php echo htmlsecure($rec["nom"]) ?></span></a>
		</span>
		<?php if ( $rec["orgid"] ) { ?>
		<span class="organisme">(
			<a class="orgnom" href="org/fiche.php?id=<?php echo intval($rec["orgid"]) ?>&view"><?php htmlsecure($rec["orgnom"]) ?></a>
			<?php if ( $rec["orgnom"] || $rec["fctdesc"] || $rec["fcttype"] ) echo ' - '; ?>
			<?php echo htmlsecure($rec["fctdesc"] ? $rec["fctdesc"] : $rec["fcttype"]) ?>
		)</span>
		<?php } ?>
		;
		<span>
		  Numéro d'opération&nbsp;: #<?php echo htmlsecure($transac) ?>
		  <span class="translinked">(#<a href="evt/bill/new-bill.php?t=<?php echo intval($oldtransac) ?>"><?php echo intval($oldtransac) ?></a><input type="hidden" name="translinked" value="<?php echo intval($oldtransac) ?>" />)</span>
		</span>
	</p>
	<div class="manifestations">
<?php
		$old = array();
		$old["nb"]	= 0;
		$old["trans"]	= 0;
		
		while ( $rec = $request->getRecordNext() )
		{
			if ( $rec["manifid"] != $old["manif"] )
			{
				$manif = array();
				$manif["sitenom"] = $rec["sitenom"];
				$manif["nom"]	= $rec["evtnom"];
				$manif["id"]	= $rec["evtid"];
				$manif["manifid"]	= $rec["manifid"];
				$manif["ville"]	= $rec["ville"];
				$manif["date"]	= $rec["date"];
				$manif["colorname"]	= $rec["colorname"];
				
				if ( $manif["old"] != 0 ) echo '</p>';
				echo '<p>';
				printManif($manif);
				
				$old["manif"] = $rec["manifid"];
				$data["manif"][] = intval($rec["manifid"]);
			}
			
			// $annul == true si on est en présence d'un enregistrement de la transaction présente
			$annul	= $transac == $rec["transaction"];
			
			// $link == true si on a déjà annulé au moins autant de billets qu'il n'y en avait de réservé
			$link = $old["nb"] < intval($rec["nb"]) || $old["trans"] != $transac;
			$old["nb"] = abs(intval($rec["nb"]));
			$old["trans"] = $rec["transaction"];
			
			if ( !$annul )
			{
				echo '<span class="resume">';
				$old["nb"]	= intval($rec["nb"]);
			}
			else	echo '<span class="resume annul">';
			echo '<span class="billet">';
			echo intval($rec["nb"])." ".htmlsecure($rec["tarif"])." ";
			echo $reduc = intval($rec["reduc"]) < 10 ? "0".intval($rec["reduc"]) : intval($rec["reduc"]);
			echo '</span>';
			echo '</span>';
			
			// récupération des places numérotées s'il y en a (on ne les récup pas avec resumetickets2print_bytransac())
			$query	= " SELECT plnum, plname
				    FROM reservation_pre AS resa, tarif, site_plnum
				    WHERE transaction = '".pg_escape_string($oldtransac)."'
				      AND annul = false
				      AND resa.plnum = site_plnum.id
				      AND (SELECT count(resa_preid) > 0 FROM reservation_cur WHERE NOT canceled AND resa_preid = resa.id)
				      AND manifid = ".intval($rec["manifid"])."
				      AND tarifid = tarif.id
				      AND tarif.key = '".pg_escape_string($rec["tarif"])."'
				    ORDER BY plnum";
			$plnum = new bdRequest($bd,$query);
			
			// version alpha d'une nouvelle méthode d'annulation de billet
			echo '<span class="valid"><span><span class="visu"></span><ul>';
			for ( $i = 0 ; $i < intval($rec["nb"]) ; $i++ )
			{
				$pl = $plnum->getRecordNext();
				echo '<li><input type="checkbox" name="billet['.intval($rec["manifid"]).'][]" value="-1'.$rec["tarif"].$reduc.':'.intval($pl['plnum']).'" /> pl. '.($pl ? "n°".$pl['plname'] : "libre").'</li>';
			}
			echo '</ul></span></span>';
		}
		
		$request->free();
		echo '</div>';
	}
?>
	<p class="valid">
		<input type="button" name="s" value="<< Départ" <?php if ( $stage <= 1 ) echo 'disabled="disabled"'; ?> class="back" onclick="javascript: location.replace('<?php echo htmlsecure($_SERVER["PHP_SELF"]) ?>');" />
		<input type="submit" name="s" value="Suivant >>" class="next" />
	</p>
	<fieldset class="hidden">
		<input type="hidden" name="filled" value="t" />
		<?php printHiddenFields($data) ?>
	</fieldset>
	</form>
</div>
<?php
	$bd->free();
	includeLib("footer");
?>
