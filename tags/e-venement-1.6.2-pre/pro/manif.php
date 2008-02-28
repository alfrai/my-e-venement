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
	includeClass("bd/group");
	includeClass("bdRequest");
	includeLib("ttt");
	includeJS("ttt");
	includeJS("annu");
	includeJS("ajax");
	
	$css[] = "pro/styles/colors.css.php";
	
	// si aucune manifestation choisie, retour à la liste des manifs
	if ( ($manifid = intval($_GET["newmanif"])) <= 0 )
	{
		$user->addAlert("Impossible d'afficher une fiche non définie.".$_SERVER["QUERY_STRING"]);
		$nav->redirect($config["website"]["base"]."pro/manifs.php");
	}
	
	// retirer un participant
	if ( intval($_GET["del"]) > 0 )
	if ( !$bd->delRecordsSimple("roadmap", array("id" => intval($_GET["del"]), "manifid" => $manifid)) )
		$user->addAlert("Impossible de retirer le participant n°".intval($_GET["del"]).".");
	
	// modifier un paiement
	if ( intval($_GET["pay"]) > 0 )
	if ( !$bd->updateRecordsRaw(	"roadmap",
					"id = ".intval($_GET["pay"])." AND manifid = ".$manifid." AND NOT is_auto_paid(manifid)",
					array("paid" => "NOT paid", "modepaiement" => $_GET["mode"] ? "'".pg_escape_string(substr($_GET["mode"],0,1))."'" : "NULL")) )
		$user->addAlert("Impossible de changer de l'état du paiement du participant n°".intval($_GET["pay"]).".");
	
	// ajouter un participant
	if ( ($fctorgid = intval(substr($_POST["client"],5))) > 0 )
	if ( !$bd->addRecordRaw("roadmap",array("fctorgid" => $fctorgid, "manifid" => $manifid, "paid" => "is_auto_paid(".$manifid.")")) )
		$user->addAlert("Impossible d'ajouter le participant n°".$fctorgid.".");
	
	// les infos de la manifestation
	$query	= " SELECT	evt.id AS evtid, manif.id AS manifid, evt.catdesc, evt.typedesc, evt.nom, manif.date,
				(SELECT nom FROM organisme WHERE id = organisme1) AS orgnom1, organisme1 AS orgid1,
				(SELECT nom FROM organisme WHERE id = organisme2) AS orgnom2, organisme2 AS orgid2,
				(SELECT nom FROM organisme WHERE id = organisme3) AS orgnom3, organisme3 AS orgid3,
				color.libelle AS colorname, site.nom AS sitenom, site.ville AS siteville, site.id AS siteid,
				(SELECT count(*) FROM roadmap WHERE manifid = manif.id) AS nbpro,
				get_contingeants(manif.id) AS cont, is_auto_paid(manif.id),
				billeterie.getprice(manif.id,(SELECT value FROM params WHERE name = 'tarifpros' LIMIT 1)) AS prix
		    FROM manifestation AS manif, evenement_categorie AS evt, colors AS color, site
		    WHERE manif.id = ".$manifid."
		      AND manif.evtid = evt.id
		      AND siteid = site.id
		      AND ( manif.colorid = color.id OR color.id IS NULL AND manif.colorid IS NULL )";
	$manif = new bdRequest($bd,$query);
	
	// extraction CSV
	if ( isset($_GET["csv"]) )
	{
		includeClass("csvExport");
		
		$arr = array();
		$i = 0;
		
		$arr[$i] = array();
		
		// entetes
		$arr[$i][] = $manif->getRecord("nom");
		$arr[$i][] = date($config["format"]["date"].' '.$config["format"]["maniftime"],strtotime($manif->getRecord("date")));
		$arr[$i][] = $manif->getRecord("sitenom").' ('.$manif->getRecord("siteville").')';
		$arr[$i][] = intval($manif->getRecord("nbpro")).' pour '.intval($manif->getRecord("cont")).' PC';
		$arr[$i][] = date($config["format"]["date"].' '.$config["format"]["ltltime"]);
		$arr[$i][] = "Prix: ".round(floatval($manif->getRecord("prix")),2)."€";
		$arr[++$i] = array();
		
		// qualification des infos
		$arr[++$i] = array();
		$arr[$i][] = "nom";
		$arr[$i][] = "prenom";
		$arr[$i][] = "organisme";
		$arr[$i][] = "code postal";
		$arr[$i][] = "ville";
		$arr[$i][] = "fonction";
		$arr[$i][] = "finances";
		
		$query	= " SELECT nom, prenom, orgnom, orgcp, orgville, fcttype, fctdesc, paid, is_auto_paid(manifid),
		            (SELECT libelle FROM modepaiement WHERE letter = roadmap.modepaiement) AS modepaiement
			    FROM personne_properso AS pers, roadmap
			    WHERE pers.fctorgid = roadmap.fctorgid
			      AND manifid = ".$manifid;
		$request = new bdRequest($bd,$query);
		while ( $rec = $request->getRecordNext() )
		{
			$arr[++$i] = array();
			$arr[$i][] = $rec["nom"];
			$arr[$i][] = $rec["prenom"];
			$arr[$i][] = $rec["orgnom"];
			$arr[$i][] = $rec["orgcp"];
			$arr[$i][] = $rec["orgville"];
			$arr[$i][] = $rec["fctdesc"] ? $rec["fctdesc"] : $rec["fcttype"];
			$arr[$i][] = $rec["is_auto_paid"] == "f" ? ($rec["paid"] == "t" ? ($rec["modepaiement"] ? $rec["modepaiement"] : "payé") : "non payé") : "";
		}
		$request->free();
		
		$csv = new csvExport($arr,isset($_GET["msoffice"]));
		$csv->printHeaders("pro-manif");
		echo $csv->createCSV();
	}
	// xHTML
	else
	{
		includeLib("headers");
		
		// export vers un groupe
		$grpid = 0;
		$grpname = "pros - manif #".$manif->getRecord("manifid")." (".$manif->getRecord("nom").")";
		if ( isset($_GET["export"]) && $user->hasRight($config["right"]["group"]) )
		{
			$desc = "Exporté le ".date($config["format"]["date"].' à '.$config["format"]["ltltime"]);
			if ( ($grpid = $bd->createGroup($grpname,$user->getId(),$desc)) > 0 )
				$user->addAlert("Un groupe statique personnel a été créé sous le nom de ".$grpname);
			else	$user->addAlert("Une erreur est survenue lors de la création ou la mise à jour de votre groupe");
		}
?>
<h1><?php echo $title ?></h1>
<div id="waiting">Calcul...</div>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
<h2>Fiche de
	<a href="evt/infos/fiche.php?id=<?php echo intval($manif->getRecord("evtid")) ?>">
	<?php echo htmlsecure($manif->getRecord("nom")); ?></a>
	<span class="org"><?php
		if ( $manif->getRecord("orgnom1") || $manif->getRecord("orgnom2") || $manif->getRecord("orgnom3") )
		{
			$i = 0;
			echo ' (';
			
			if ( intval($manif->getRecord("orgid1")) > 0 )
			{
				echo '<a href="org/fiche.php?id='.intval($manif->getRecord("orgid1")).'">'.htmlsecure($manif->getRecord("orgnom1")).'</a>';
				$i++;
			}
			
			if ( intval($manif->getRecord("orgid2")) > 0 )
			{
				$i++;
				if ( $i > 1 ) echo ', ';
				echo '<a href="org/fiche.php?id='.intval($manif->getRecord("orgid2")).'">'.htmlsecure($manif->getRecord("orgnom2")).'</a>';
			}
			
			if ( intval($manif->getRecord("orgid3")) > 0 )
			{
				$i++;
				if ( $i > 1 ) echo ', ';
				echo '<a href="org/fiche.php?id='.intval($manif->getRecord("orgid3")).'">'.htmlsecure($manif->getRecord("orgnom3")).'</a>';
			}
			
			echo ')';
		}
	?></span>
</h2>
<h3><?php
	$time = strtotime($manif->getRecord("date"));
	echo 'Le <a href="evt/infos/manif.php?id='.intval($manif->getRecord("manifid")).'&evtid='.intval($manif->getRecord("evtid")).'">';
	echo htmlsecure($config["dates"]["dotw"][date("w",$time)]." ".date($config["format"]["date"]." ".$config["format"]["maniftime"],$time));
	echo '</a>';
	echo ' à <a href="evt/infos/salle.php?id='.intval($manif->getRecord("siteid")).'">'.htmlsecure($manif->getRecord("sitenom")).'</a>';
	echo ' ('.htmlsecure($manif->getRecord("siteville")).')';
	echo ' - ';
	echo intval($manif->getRecord("nbpro")).'/'.intval($manif->getRecord("cont"));
	echo ' - ';
	echo htmlsecure(round($prix = floatval($manif->getRecord("prix")),2)."€");
?></h3>
<form class="pro" name="formu" action="<?php echo htmlsecure($_SERVER["PHP_SELF"]) ?>?newmanif=<?php echo $manifid ?>&view" method="post">
<p class="roadmap">Feuille de manifestation&nbsp;:</p>
<fieldset class="hidden">
	<input type="hidden" id="desc" name="desc" value="<?php
		echo htmlsecure("Pour plus d'aisance, cliquer sur ce lien de manière à l'ouvrir dans un nouvel onglet... (ctrl+clic)")
		?>" />
</fieldset>
<ul class="pros">
	<?php
		// récup des modes de paiement
		$query  = " SELECT * FROM modepaiement ORDER BY libelle";
		$request = new bdRequest($bd,$query);
		$modepaiement = array();
		while ( $rec = $request->getRecordNext() )
			$modepaiement[$rec["letter"]] = $rec["libelle"];
		$request->free();
		
		// affichage des pros déjà inscrits
		$pers	= " SELECT personne.id, personne.nom, personne.prenom, fct.id AS fctorgid,
			           organisme.nom AS orgnom, organisme.id AS orgid,
			           fonction.libelle AS fcttype, fonction AS fctdesc,
			           roadmap.id AS rmid, roadmap.paid, roadmap.modepaiement
			    FROM roadmap, org_personne AS fct, personne, organisme,
			         (SELECT id,libelle FROM fonction UNION SELECT NULL AS id, NULL AS libelle) AS fonction
			    WHERE manifid = ".$manifid."
			      AND roadmap.fctorgid = fct.id
			      AND organisme.id = fct.organismeid
			      AND personne.id = fct.personneid
			      AND ( fonction.id = fct.type OR fonction.id IS NULL AND fct.type IS NULL )
			    ORDER BY nom, prenom, orgnom, rmid";
		$request = new bdRequest($bd,$pers);
	
		$total["du"] = 0;
		$total["paye"] = 0;
		while ( $rec = $request->getRecordNext() )
		{
			echo '<li>';
			
			// ajout de la personne dans le groupe
			if ( $grpid > 0 )
				$bd->addGroupPro($grpid,intval($rec["fctorgid"]));
			
			echo '<span class="actions">';
			
			// supprimer
			echo '<a class="del"';
			if ( $user->prolevel >= $config["pro"]["right"]["mod"] )
			echo ' href="'.$_SERVER["PHP_SELF"].'?newmanif='.$manifid.'&del='.intval($rec["rmid"]).'&view"';
			echo '>&nbsp;<span>supprimer</span></a>';
			echo '<span class="desc">Retirer la personne</span>';
			
			// payer
			if ( $manif->getRecord("is_auto_paid") == 'f' )
			{
				echo '<a class="'.($rec["paid"] == 't' ? "paid" : "pay").'"';
				if ( $user->prolevel >= $config["pro"]["right"]["mod"] && $rec["paid"] == 't' )
				echo '	 href="'.$_SERVER["PHP_SELF"].'?newmanif='.$manifid.'&pay='.intval($rec["rmid"]).'&view"';
				echo '>'.($rec["paid"] == 't' ? ($rec["modepaiement"] ? htmlsecure($rec["modepaiement"]) : "€" ) : "&nbsp;").'<span>payer/impayer</span></a>';
				if ( $user->prolevel >= $config["pro"]["right"]["mod"] && $rec["paid"] == 'f' )
				{
					echo '<div class="desc">
						Payer par&nbsp;:<ul>';
						foreach ( $modepaiement as $letter => $libelle )
						echo '<li><a href="'.$_SERVER["PHP_SELF"].'?newmanif='.$manifid.'&pay='.intval($rec["rmid"]).'&mode='.$letter.'&view">'.$libelle.'</a> ('.$letter.')</li>';
						echo '<li><a href="'.$_SERVER["PHP_SELF"].'?fctorgid='.$manifid.'&pay='.intval($rec["rmid"]).'&mode=€&view">inconnu</a> (€)</li>';
					echo '</ul></div>';
				}
				
				// gestion des credits
				$total["du"] += $prix;
				if ( $rec["paid"] == 't' ) $total["paye"] += $prix;
			}
			echo '</span>';
			
			echo '<span><a href="ann/fiche.php?id='.intval($rec["id"]).'" class="pers '.($rec["npai"] == 't' ? 'npai' : '').'">';
			echo htmlsecure($rec["nom"].' '.$rec["prenom"]).'</a></span>';
			echo '<span><a href="pro/pro.php?fctorgid='.intval($rec["fctorgid"]).'" class="manifpro"><span>fiche pro</span></a></span>';
			echo '<span>(<a href="org/fiche.php?id='.intval($rec["orgid"]).'" class="org">'.htmlsecure($rec["orgnom"]).'</a>';
			echo ' - '.htmlsecure($rec["fctdesc"] ? $rec["fctdesc"] : $rec["fcttype"]).')</span>';
			
			echo '</li>';
		}
	echo '<li class="total"><span>Total&nbsp;: '.round($total["du"],2).'€ - Reste à payer&nbsp;: '.round($total["du"]-$total["paye"],2).'€</span>&nbsp;</li>';
		
		$request->free();
?>		
</ul>
<?php
		if ( $user->prolevel >= $config["pro"]["right"]["mod"] )
		{
	?>
<div>
	Nouveau participant&nbsp;: <input type="text" name="pers" value="" onkeyup="javascript: annu_bill(this,true,true,true);" />
	<p class="newpers" id="personnes"></p>
</div>
	<?php	} ?>
</ul>
</form>
<p class="grpexp">
	<span><a href="pro/manif.php?newmanif=<?php echo $manifid ?>&export">Exporter</a> vers un groupe de l'annuaire...</span>
	<?php if ( $grpid > 0 ) { ?>
	<span><a href="ann/search.php?grpid=<?php echo $grpid ?>&grpname=<?php echo urlencode($grpname) ?>">Consulter</a> le groupe créé...</span>
	<?php } ?>
</p>
<p class="csvext">
	<span>Extraction <a href="pro/manif.php?newmanif=<?php echo $manifid ?>&csv">standard</a>...</span>
	<span>Extraction <a href="pro/manif.php?newmanif=<?php echo $manifid ?>&csv&msoffice">compatible Microsoft</a>...</span>
</p>
</div>
<?php
		includeLib("footer");
	}
	$manif->free();
	$bd->free();
?>
