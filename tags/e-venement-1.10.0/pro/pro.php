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
	includeLib("ttt");
	includeJS("ttt");
	includeJS("ajax");
	includeJS("jauge","pro");
	
	$css[] = "pro/styles/colors.css.php";
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	$action = $actions["edit"];
	
	// valeurs par défaut (la clé du tableau doit etre la même que la clé du tableau passé en POST)
	$default["nom"] = "-DUPORT-";
	
	// si personne de choisi, retour à la liste des pros
	if ( ($fctorgid = intval($_GET["fctorgid"])) <= 0 )
	{
		$user->addAlert("Impossible d'afficher une fiche non définie.".$_SERVER["QUERY_STRING"]);
		$nav->redirect($config["website"]["base"]."pro/pros.php");
	}
	
	if ( $user->prolevel >= $config["pro"]["right"]["mod"] )
	{
		// les ajouts de manif
		$err = 0;	// gestion des erreurs
		if ( is_array($_POST["newmanif"]) && $_POST["newmanif"] )
		foreach ( $_POST["newmanif"] as $newmanif )
		if ( !$bd->addRecordRaw("roadmap",array("fctorgid" => $fctorgid,
							"manifid" => intval($newmanif),
							"paid" => "is_auto_paid(".intval($newmanif).")")) )
			$err++;
		if ( $err > 0 )	$user->addAlert("Impossible d'ajouter ".$err.' manifestation(s). Peut-être manque-t-il la définition du tarif "pro" ?');
		
		// retrait de manif
		if ( intval($_GET["del"]) > 0 )
		if ( !$bd->delRecordsSimple("roadmap",array("fctorgid" => $fctorgid, "id" => intval($_GET["del"]))) )
			$user->addAlert("Impossible de supprimer la manifestation.");
		
		// noter la manifestation comme payée
		if ( intval($_GET["pay"]) > 0 )
		if ( !$bd->updateRecordsRaw(	"roadmap",
						"fctorgid = ".$fctorgid." AND id = ".intval($_GET["pay"])." AND NOT is_auto_paid(manifid)",
						array("paid" => "NOT paid","modepaiement" => $_GET["mode"] ? "'".pg_escape_string(substr($_GET["mode"],0,1))."'" : "NULL" )) )
			$user->addAlert("Impossible de mettre à jour le paiement.");
	}
		
	// les infos de la personne
	$query	= " SELECT personne.nom, personne.prenom, personne.id, organisme.nom AS orgnom, organisme.id AS orgid,
		           organisme.ville AS orgville, organisme.adresse AS orgadr, organisme.cp AS orgcp
		    FROM org_personne AS fctorg, personne, organisme
		    WHERE personne.id = fctorg.personneid
		      AND fctorg.organismeid = organisme.id
		      AND fctorg.id = ".$fctorgid;
	$personne = new bdRequest($bd,$query);
	
	if ( isset($_GET["csv"]) )
	{
		includeClass("csvExport");
		
		$arr = array();
		$i = 0;
		
		$arr[$i] = array();
		
		// entetes
		$arr[$i][] = $personne->getRecord("nom");
		$arr[$i][] = $personne->getRecord("prenom");
		$arr[$i][] = $personne->getRecord("orgnom");
		$arr[$i][] = trim($personne->getRecord("orgadr"));
		$arr[$i][] = $personne->getRecord("orgcp");
		$arr[$i][] = $personne->getRecord("orgville");
		$arr[$i][] = date($config["format"]["date"].' '.$config["format"]["ltltime"]);
		$arr[++$i] = array();
		
		// qualification des infos
		$arr[++$i] = array();
		$arr[$i][] = "date";
		$arr[$i][] = "evenement";
		$arr[$i][] = "paiement";
		$arr[$i][] = "prix";
		$arr[$i][] = "genre";
		$arr[$i][] = "nb de pros";
		$arr[$i][] = "contingents";
		$arr[$i][] = "compagnie";
		$arr[$i][] = "...";
		
		$manifs	= " SELECT	evt.id AS evtid, manif.id AS manifid, evt.catdesc, evt.typedesc, evt.nom, manif.date,
					roadmap.paid, is_auto_paid(manif.id),
					(SELECT libelle FROM modepaiement WHERE letter = roadmap.modepaiement) AS modepaiement,
					billeterie.getprice(manif.id,(SELECT value FROM params WHERE name = 'tarifpros' LIMIT 1)) AS prix,
					(SELECT nom FROM organisme WHERE id = organisme1) AS orgnom1, organisme1 AS orgid1,
					(SELECT nom FROM organisme WHERE id = organisme2) AS orgnom2, organisme2 AS orgid2,
					(SELECT nom FROM organisme WHERE id = organisme3) AS orgnom3, organisme3 AS orgid3,
					(SELECT count(*) FROM roadmap WHERE manifid = manif.id) AS nbpro,
					(SELECT get_contingeants(manif.id)) AS cont, 
					site.nom AS sitenom, site.ville AS siteville, site.id AS siteid
			    FROM roadmap, manifestation AS manif, evenement_categorie AS evt, site
			    WHERE fctorgid = ".$fctorgid."
			      AND manif.evtid = evt.id
			      AND roadmap.manifid = manif.id
			      AND site.id = manif.siteid
			    ORDER BY date";
		$request = new bdRequest($bd,$manifs);
		while ( $rec = $request->getRecordNext() )
		{
			$arr[++$i] = array();
			$arr[$i][] = date($config["format"]["date"]." ".$config["format"]["maniftime"],strtotime($rec["date"]));
			$arr[$i][] = $rec["nom"];
			$arr[$i][] = $rec["is_auto_paid"] == 'f' ? ($rec["paid"] == "t" ? ($rec["modepaiement"] ? $rec["modepaiement"] : "payé") : "non payé") : "";
			$arr[$i][] = round(floatval($rec["prix"]),2);
			$arr[$i][] = $rec["typedesc"] ? $rec["typedesc"] : $rec["catdesc"];
			$arr[$i][] = intval($rec["nbpro"]);
			$arr[$i][] = intval($rec["cont"]);
			if ( $rec["orgnom1"] )
			$arr[$i][] = $rec["orgnom1"];
			if ( $rec["orgnom2"] )
			$arr[$i][] = $rec["orgnom2"];
			if ( $rec["orgnom3"] )
			$arr[$i][] = $rec["orgnom3"];
		}
		$request->free();
		
		$csv = new csvExport($arr,isset($_GET["msoffice"]));
		$csv->printHeaders("pro-roadmap");
		echo $csv->createCSV();
	}
	else
	{
		includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<div id="waiting">Calcul...</div>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
<h2>Fiche de <a href="ann/fiche.php?id=<?php echo intval($personne->getRecord("id")) ?>">
<?php echo htmlsecure($personne->getRecord("nom")." ".$personne->getRecord("prenom")); ?></a>
(<?php echo '<a href="'.intval($personne->getRecord("orgid")).'">'.$personne->getRecord("orgnom").'</a>' ?>)</h2>
<form class="pro" name="formu" action="<?php echo htmlsecure($_SERVER["PHP_SELF"]) ?>?fctorgid=<?php echo $fctorgid ?>&view" method="post">
<p class="roadmap">Feuille de route&nbsp;:</p>
<ul class="manifs"><?php
		// récup des modes de paiement
		$query	= " SELECT * FROM modepaiement ORDER BY libelle";
		$request = new bdRequest($bd,$query);
		$modepaiement = array();
		while ( $rec = $request->getRecordNext() )
			$modepaiement[$rec["letter"]] = $rec["libelle"];
		$request->free();
		
		// affichage des manifestations déjà prévues
		$manifs	= " SELECT	evt.id AS evtid, manif.id AS manifid, evt.categorie, evt.catdesc, evt.typedesc, evt.nom, manif.date,
					(SELECT nom FROM organisme WHERE id = organisme1) AS orgnom1, organisme1 AS orgid1,
					(SELECT nom FROM organisme WHERE id = organisme2) AS orgnom2, organisme2 AS orgid2,
					(SELECT nom FROM organisme WHERE id = organisme3) AS orgnom3, organisme3 AS orgid3,
					color.libelle AS colorname, roadmap.id, roadmap.paid, (SELECT count(*) FROM roadmap WHERE manifid = manif.id) AS nbpro,
					site.nom AS sitenom, site.ville AS siteville, site.id AS siteid, is_auto_paid(manif.id),
					modepaiement, billeterie.getprice(manif.id,(SELECT value FROM params WHERE name = 'tarifpros' LIMIT 1)) AS prix
			    FROM roadmap, manifestation AS manif, evenement_categorie AS evt, colors AS color, site
			    WHERE fctorgid = ".$fctorgid."
			      AND manif.evtid = evt.id
			      AND roadmap.manifid = manif.id
			      AND ( manif.colorid = color.id OR color.id IS NULL AND manif.colorid IS NULL )
			      AND site.id = manif.siteid
			    ORDER BY catdesc, date, id";
		$request = new bdRequest($bd,$manifs);
		
		$oldcat = 0;
		$total["prix"] = 0;
		$total["paye"] = 0;
		while ( $rec = $request->getRecordNext() )
		{
			$time = strtotime($rec["date"]);
			
			// nouvelle catégorie
			if ( intval($rec["categorie"]) != $oldcat )
			{
				if ( $oldcat != 0 )
				echo '</ul></li>';
				echo '<li>'.htmlsecure($rec["catdesc"]).'<ul>';
				
				$oldcat = intval($rec["categorie"]);
			}
			
			echo '<li>';
			echo floatval($rec["prix"]) > 0 ? '<span class="prix">'.round(floatval($rec["prix"]),2)."€</span>" : '';
			$total["prix"] += $rec["prix"];
			// supprimer
			echo '<span class="actions">';
			echo '<a class="del"';
			if ( $user->prolevel >= $config["pro"]["right"]["mod"] )
			echo ' href="'.$_SERVER["PHP_SELF"].'?fctorgid='.$fctorgid.'&del='.intval($rec["id"]).'&view"';
			echo '>&nbsp;<span>supprimer</span></a>';
			echo '<span class="desc">Retirer la manifestation</span>';
			
			// payer
			if ( $rec["is_auto_paid"] == "f" )
			{
				echo '<a class="'.($rec["paid"] == 't' ? "paid" : "pay").'"';
				if ( $user->prolevel >= $config["pro"]["right"]["mod"] && $rec["paid"] == 't' )
				echo '	 href="'.$_SERVER["PHP_SELF"].'?fctorgid='.$fctorgid.'&pay='.intval($rec["id"]).'&view"';
				echo '>'.($rec["paid"] == 't' ? ($rec["modepaiement"] ? htmlsecure($rec["modepaiement"]) : "€" ) : "&nbsp;").'<span>payer/impayer</span></a>';
				if ( $user->prolevel >= $config["pro"]["right"]["mod"] && $rec["paid"] == 'f' )
				{
					echo '<div class="desc">
						Payer par&nbsp;:<ul>';
						foreach ( $modepaiement as $letter => $libelle )
						echo '<li><a href="'.$_SERVER["PHP_SELF"].'?fctorgid='.$fctorgid.'&pay='.intval($rec["id"]).'&mode='.$letter.'&view">'.$libelle.'</a> ('.$letter.')</li>';
						echo '<li><a href="'.$_SERVER["PHP_SELF"].'?fctorgid='.$fctorgid.'&pay='.intval($rec["id"]).'&mode=€&view">inconnu</a> (€)</li>';
					echo '</ul></div>';
				}
				
				// prise en compte de ce qui est payé
				if ( $rec["paid"] == 't' )
					$total["paye"] += floatval($rec["prix"]);
			}
			else	echo '<span class="fakepay"></span>';
			
			echo '<span class="date">le <a href="evt/infos/manif.php?id='.intval($rec["manifid"]).'&evtid='.intval($rec["evtid"]).'" class="'.htmlsecure($rec["colorname"]).' manif">';
			echo $config["dates"]["dotw"][date("w",$time)].' '.date($config["format"]["date"].' à '.$config["format"]["maniftime"],$time).'</a></span>';
			echo '<span class="fiche"><a href="pro/manif.php?newmanif='.intval($rec["manifid"]).'" class="manifpro"><span>fiche manifestation</span></a></span>';
			echo '<span class="quota">'.intval($rec["nbpro"]).'/<span class="jauge" onmouseover="javascript: get_nbcontingeants(this,'.intval($rec["manifid"]).');">..</span></span>';
			
			echo '<span class="evt"><a href="evt/infos/fiche.php?id='.intval($rec["evtid"]).'">'.htmlsecure($rec["nom"]).'</a>';
			
			echo '<span class="site"><a href="evt/infos/salle.php?id='.intval($rec["siteid"]).'">'.htmlsecure($rec["sitenom"]).'</a> ('.htmlsecure($rec["siteville"]).')</site>';
			
			echo '</li>';
		}
		if ( $oldcat != 0 )
			echo '</ul></li>';
		
		echo '<li>Total<ul><li><span class="prix total">'.round($total["prix"],2).'€</span><span class="paye">Manque à payer&nbsp;: '.round($total["prix"]-$total["paye"],2).'€</span></li></ul></li>';
		
		$request->free();
?></ul>
<?php		
		if ( $user->prolevel >= $config["pro"]["right"]["mod"] )
		{
?>
<div>
	Nouvelle manifestation&nbsp;:
	<div class="newmanif"><?php includePage("manifs"); ?></div>
	<p><input type="submit" name="submit" value="Ajouter" /></p>
<?php		} ?>
</form>
<p class="csvext">
	<span>Extraction <a href="pro/pro.php?fctorgid=<?php echo $fctorgid ?>&csv">standard</a>...</span>
	<span>Extraction <a href="pro/pro.php?fctorgid=<?php echo $fctorgid ?>&csv&msoffice">compatible Microsoft</a>...</span>
</p>
</div>
<?php
		includeLib("footer");
	}
	$personne->free();
	$bd->free();
?>
