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
	$css[] = "evt/styles/colors.css.php";
	$class = "evt recaps";
	
	includeClass("bd/array");
	includeClass("bdRequest/array");
	includeLib("ttt");
	includeLib("bill");
	
	$jauge = true;
	$subtitle = "Extractions d'informations concernant les évènements...";
	
	$date["start"]	= $_GET["b"]["value"] ? $_GET["b"]["value"] : date("Y-m-d",strtotime("now"));
	$date["stop"]	= $_GET["e"]["value"] ? $_GET["e"]["value"] : date("Y-m-d",strtotime("now + 1 month"));
	
	includeClass("csvExport");
	
	switch ( $_GET["case"] ) {
	case "ventes":
		$arr = array();
		$vals = array();
		$i = -1;
		
		// echappement et vérification des couleurs données
		$couleur = array();
		if ( is_array($_GET["couleur"]) )
		foreach ( $_GET["couleur"] as $value )
			$couleur[] = intval($value) > 0 ? " = ".intval($value) : " IS NULL";
		
		$arr[++$i] = array();
		$arr[$i][] = "Evenement";
		$arr[$i][] = "Date";
		$arr[$i][] = "Lieu";
		$arr[$i][] = "Jauge totale";
		$vals['total-total'] = count($arr[$i]) - 1;
		$arr[$i][] = "Res. totales";
		$vals['total-vendu'] = count($arr[$i]) - 1;
		$arr[$i][] = "Reste à vendre";
		$vals['total-reste'] = count($arr[$i]) - 1;
		$arr[$i][] = "Jauge interne";
		$vals['perso-total'] = count($arr[$i]) - 1;
		$arr[$i][] = "BDC interne";
		$vals['perso-bdc'] = count($arr[$i]) - 1;
		$arr[$i][] = "Contingents";
		$vals['contingents'] = count($arr[$i]) - 1;
		$arr[$i][] = "Vendus interne";
		$vals['perso-vendu'] = count($arr[$i]) - 1;
		$arr[$i][] = "Reste en interne";
		$vals['perso-reste'] = count($arr[$i]) - 1;
		$arr[$i][] = "Dépôt partenaires";
		$vals['depot-total'] = count($arr[$i]) - 1;
		$arr[$i][] = "Vendus partenaires";
		$vals['depot-vendu'] = count($arr[$i]) - 1;
		$arr[$i][] = "Reste aux partenaires";
		$vals['depot-reste'] = count($arr[$i]) - 1;
		
		/*
		$query	= " SELECT evt.id AS evtid, manif.id AS manifid, site.nom AS sitenom, site.ville AS siteville,
			           evt.nom, manif.date, CASE WHEN ".($_GET['spaces'] == 'all' ? 'true' : 'false')." THEN manif.jauge + sum(CASE WHEN sm.jauge IS NULL THEN manif.jauge ELSE sm.jauge END as jauge,
			           evt.catdesc, evt.typedesc, evt.metaevt
			    FROM evenement_categorie AS evt, site, manifestation AS manif
			    LEFT JOIN space_manifestation sm ON sm.manifid = manif.id AND sm.spaceid ".($user->evtspace ? ' = '.$user->evtspace : ' IS NULL')."
			    WHERE manif.evtid = evt.id
			      AND manif.siteid = site.id
			      AND date <= '".pg_escape_string($date["stop"])."'::date + '1 day'::interval
			      AND date >= '".pg_escape_string($date["start"])."'::date";
	  */
	  $query = "SELECT id AS evtid, manifid, sitenom, ville as siteville,
			           nom, date, sum(jauge) AS jauge, catdesc, typedesc,
			           (SELECT metaevt FROM evenement e WHERE e.id = id LIMIT 1) AS metaevt
	            FROM info_resa
	            WHERE date <= '".pg_escape_string($date["stop"])."'::date + '1 day'::interval
	              AND date >= '".pg_escape_string($date["start"])."'::date
	              ".($_GET['spaces'] != 'all' ? 'AND spaceid '.($user->evtspace ? '= '.$user->evtspace : 'IS NULL') : '');
		if ( count($couleur) > 0 ) $query .= " AND ( colorid".implode(" OR colorid",$couleur).")";
		$query .= " GROUP BY id, manifid, sitenom, ville, nom, date,catdesc, typedesc";
		if ( $_GET["ordre"] == "nom" )
			$query .= " ORDER BY nom, date";
		else	$query .= " ORDER BY date, nom";
		$request = new bdRequest($bd,$query);
		
		while ( $rec = $request->getRecordNext() )
		{
			$query	= " SELECT manifid, sum(nb) AS nb, transac.id IN (SELECT transaction FROM contingeant) AS contingeant,
				           transac.id IN (SELECT transaction FROM masstickets) AS depot, printed AND NOT canceled AS resa,
				           transac.id IN (SELECT transaction FROM preselled) AS preresa
				    FROM tickets2print_bymanif(".intval($rec["manifid"]).") AS resa, transaction AS transac
				    WHERE transac.id = resa.transaction
				      ".($_GET['spaces'] != 'all' ? "AND transac.spaceid ".($user->evtspace ? ' = '.$user->evtspace : ' IS NULL') : '')."
				    GROUP BY contingeant, resa, preresa, manifid, depot";
			$infos = new bdRequest($bd,$query);
			
	 		$arr[++$i] = array();
	 		$arr[$i][] = $rec["nom"];
	 		$time = strtotime($rec["date"]);
	 		$arr[$i][] = $config["dates"]["dotw"][date('w',$time)].' '.date($config["format"]["date"].' '.$config["format"]["maniftime"],$time);
	 		$arr[$i][] = $rec["sitenom"].' ('.$rec["siteville"].')';
	 		
	 		for ( $j = 0 ; $j < count($vals) ; $j++ )
	 			$arr[$i][] = 0;
	 		
	 		// renseignements trouvés en base
	 		while ( $elt = $infos->getRecordNext() )
	 		{
	 			unset($j);
		 		if ( $elt["contingeant"] == "t" )
		 		{
		 			if ( $elt["depot"] == "t" )
		 			{
		 				if ( $elt["resa"] == "t" )
		 					$j = $vals["depot-vendu"];
			 			else	$j = $vals["depot-reste"];
		 			}
		 			else	$j = $vals["contingents"];
		 		}
		 		else
		 		{
		 			if ( $elt["resa"] == "t" )
		 				$j = $vals["perso-vendu"];
		 			elseif ( $elt["preresa"] == "t" )
		 				$j = $vals["perso-bdc"];
		 		}
				
				if ( isset($j) )
		 		$arr[$i][$j] += intval($elt["nb"]);
		 	}
	 		
	 		// calculs déduits
	 		$arr[$i][$vals["depot-total"]]	= $arr[$i][$vals["depot-reste"]] + $arr[$i][$vals["depot-vendu"]];
	 		$arr[$i][$vals["perso-total"]]	= intval($rec["jauge"]) - $arr[$i][$vals["depot-total"]];
	 		$arr[$i][$vals["perso-reste"]]	= $arr[$i][$vals["perso-total"]] - $arr[$i][$vals["perso-vendu"]] - $arr[$i][$vals["perso-bdc"]] - $arr[$i][$vals["contingents"]];
	 		$arr[$i][$vals["total-total"]]	= intval($rec["jauge"]);
	 		$arr[$i][$vals["total-vendu"]]	= $arr[$i][$vals["perso-vendu"]] + $arr[$i][$vals["depot-vendu"]] + $arr[$i][$vals["perso-bdc"]] + $arr[$i][$vals["contingents"]];
	 		$arr[$i][$vals["total-reste"]]	= $arr[$i][$vals["total-total"]] - $arr[$i][$vals["total-vendu"]];
	 		
	 		$infos->free();
	 	}
	 	$request->free();
	 	
	 	$csv = new csvExport($arr,isset($_GET["msexcel"]));
	 	$csv->printHeaders("recap");
	 	echo $csv->createCSV();
	 	
	 	break;
	 case "metaevt":
	 	$bd->beginTransaction();
	 	
	 	// creation initiale du groupe
	 	$grpname = "[metaevt] ".$_GET["metaevt"];
	 	$arrgrp = array("nom" => $grpname, "createur" => $user->getId());
	 	$bd->delRecordsSimple("groupe",$arrgrp);
	 	$bd->addRecord("groupe",$arrgrp);
	 	$grpid = $bd->getLastSerial("groupe","id");
	 	
	 	$baseq	= " FROM evenement AS evt, manifestation AS manif,
	 		         billeterie.transaction, reservation_cur AS resa, reservation_pre AS preresa
	 		    WHERE metaevt = '".pg_escape_string($_GET["metaevt"])."'
	 		      AND preresa.id = resa.resa_preid
	 		      AND preresa.manifid = manif.id
	 		      AND manif.evtid = evt.id
	 		      AND transaction.id = preresa.transaction
	 		      AND NOT canceled";
	 		      
	 	// ajout des personnes dans le groupe
	 	$query	= " SELECT DISTINCT transaction.personneid, true AS included, ".$grpid." AS groupid
	 		  ".$baseq."
	 		      AND transaction.fctorgid IS NULL
	 		      AND transaction.personneid IS NOT NULL";
	 	$nbpers = $bd->addRecordsQuery("groupe_personnes",array("personneid", "included", "groupid"),$query);
	 	
	 	// ajout des pros dans le groupe
	 	$query	= " SELECT DISTINCT transaction.fctorgid AS fonctionid, true AS included, ".$grpid." AS groupid
	 		  ".$baseq."
	 		      AND transaction.fctorgid IS NOT NULL";
	 	$nbpro = $bd->addRecordsQuery("groupe_fonctions",array("fonctionid", "included", "groupid"),$query);
	 	
	 	if ( $bd->getTransactionStatus() )
	 	{
	 		$user->addAlert($nbpers." personne(s) ajoutée(s) au groupe ".$grpname);
	 		$user->addAlert($nbpro." professionnel(s) ajouté(s) au groupe ".$grpname);
	 		
	 		$url = $config["website"]["root"].'ann/new-search.php?grpid='.$grpid.'&grpname='.$grpname;
	 	}
	 	else	$url = 'evt/infos/exts.php';
	 	
	 	$bd->endTransaction();
	 	$nav->redirect($url);
	 	
	 	break;
	 default:
	 	includeJS("ajax");
	 	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
<h2><?php echo htmlsecure($subtitle) ?></h2>
<div class="sommaire">
	<p class="nom">Sommaire</p>
	<p><a href="<?php echo htmlsecure($_SERVER["PHP_SELF"]) ?>#ventes">Récapitulatif de l'état des jauges</a></p>
	<p><a href="<?php echo htmlsecure($_SERVER["PHP_SELF"]) ?>#metaevt">Personnes présentes pour un meta-évènement</a></p>
</div>
<a name="ventes"></a>
<h3>État des jauges des différentes manifestations</h3>
<form action="<?php echo htmlsecure($_SERVER["PHP_SELF"]) ?>" method="get">
	<p class="date">
		<input type="hidden" name="case" value="ventes" />
		<span class="debut">À partir du <?php printField("b",$date["start"],$default["date"],22,17) ?></span>,
		<span class="fin">jusqu'au <?php printField("e",$date["stop"],$default["date"],22,17) ?></span>
	</p>
	<p class="ordre">
		<span class="label">Ordonnancement par&nbsp;:</span>
		<span class="choix"><input type="radio" name="ordre" checked="checked" value="date" />date puis nom</span>
		<span class="choix"><input type="radio" name="ordre" value="nom" />nom puis date</span>
	</p>
	<div class="couleur">
		<span class="label">Choisir des couleurs&nbsp;:</span>
		<ul><?php
			$query = " SELECT * FROM colors";
			$request = new bdRequest($bd,$query);
			
			while ( $rec = $request->getRecordNext() )
			{
				echo '<li class="choix '.htmlsecure($rec["libelle"]).'">';
				echo '<input type="checkbox" name="couleur[]" value="'.intval($rec["id"]).'" />';
				echo '<span class="hidden">&nbsp;'.htmlsecure($rec["libelle"]).'</span>';
				echo '</li>';
			}
		?></ul>
	</div>
	<p class="submit">
		<span class="submit"><input type="submit" name="ventes" value="Extraire" /></span>
		<span class="msoffice" title="Compatibilité MSExcel" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
			<input type="checkbox" name="msexcel" value="yes" onclick="javascript: ttt_spanCheckBox(this);" />
			Compatibilité MSExcel
		</span>
	  <span class="spaces" title="Voir tous les espaces"><input type="checkbox" name="spaces" value="all" />&nbsp;Voir tous les espaces</span>
	</p>
</form>
<form action="<?php echo htmlsecure($_SERVER["PHP_SELF"]) ?>" method="get" class="metaevt">
	<a name="metaevt"></a>
	<h3>Export des personnes présentes pour un meta-évènement</h3>
	<p><select name="metaevt"><?php
		$query	= " SELECT DISTINCT metaevt FROM evenement ORDER BY metaevt";
		$request = new bdRequest($bd,$query);
		
		while ( $rec = $request->getRecordNext() )
		if ( $rec["metaevt"] != "" )
			echo '<option>'.htmlsecure($rec["metaevt"]).'</option>';
		
		$request->free();
	?></select></p>
	<p class="submit">
		<input type="hidden" name="case" value="metaevt" />
		<span class="submit"><input type="submit" name="submit" value="Exporter" /></span>
		<span class="desc">(redirige automatiquement vers le groupe nouvellement créé)</span>
	</p>
	
</form>
</div>
<?php
	 		includeLib("footer");	
	 		break;
	} // switch ( $_GET["case"] )
	$bd->free();
?>
