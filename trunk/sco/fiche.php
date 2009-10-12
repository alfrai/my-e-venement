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
	includeLib("sco");
	includeJS("ajax");
	includeJS("ttt");
	includeJS("jquery");
	includeJS("sco","sco");
	includeLib("bill");
	includeLib("jauge");
	
	$css[] = "sco/styles/jauge.css";
	$css[] = "sco/styles/colors.css.php";
	
	// gestion primaire des droits
	if ( $user->scolevel < $config["sco"]["right"]["view"] )
		$nav->redirect($config["website"]["base"]);
	if ( $user->scolevel < $config["sco"]["right"]["mod"] && (isset($_GET["add"]) || isset($_GET["del"])) )
		$nav->redirect($config["website"]["base"]."sco");
	
	$id = intval($_GET["id"]);
	
	// completion du tableau
	if ( (is_array($_POST["newmanif"]) || is_array($_POST["newclient"])) && $user->scolevel >= $config["sco"]["right"]["mod"] )
	{
		$ok = true;
		if ( $id <= 0 )
			$ok = $bd->addRecord("tableau",array("accountid" => $user->getId()));
		if ( !$ok )
		{
			$user->addAlert("Création du tableau impossible");
			$_GET["add"] = true;
		}
		else
		{
			// récup de l'id du tableau en cas de création
			if ( $id <= 0 ) $id = $bd->getLastSerial("tableau","id");
			
			// ajout des colonnes (manifs)
			if ( is_array($_POST["newmanif"]) )
			foreach ( $_POST["newmanif"] as $manif )
			if ( intval($manif) > 0 )
			if ( !$bd->addRecord("tableau_manif",array("tableauid" => $id, "manifid" => intval($manif))) )
				$user->addAlert("Impossible d'ajouter la manifestation ".htmlsecure($manif));
			
			// ajout des lignes (clients)
			if ( is_array($_POST["newclient"]) )
			foreach ( $_POST["newclient"] as $client )
			{
				$status = substr($client,0,4);
				if ( $status == "prof" )
				{
					$proid = intval(substr($client,5));
					$query	= " SELECT personneid AS id FROM org_personne WHERE id = ".$proid;
					$request = new bdRequest($bd,$query);
					$persid = intval($request->getRecord("id"));
					$request->free();
				}
				else
				{
					$proid = NULL;
					$persid = intval(substr($client,5));
				}
				
				if ( $persid > 0 )
				if ( !$bd->addRecord("tableau_personne",array("tableauid" => $id, "personneid" => $persid, "fctorgid" => $proid)) )
					$user->addAlert("Impossible d'ajouter l'utilisateur n°".$persid);
			}
		}
	}
	
	// MAJ des lignes pour les confirmation
	if ( is_array($_POST["confirmed"]) )
	{
		// rien n'est confirmé
		if ( !$bd->updateRecordsSimple("tableau_personne",array("tableauid" => $id),array("confirmed" => "f")) )
			$user->addAlert("Impossible de remettre à zéro les confirmations");
		
		// sauf ce qui est explicitement indiqué
		$err = 0;
		foreach ( $_POST["confirmed"] as $key => $value )
		if ( intval($key) > 0 )
		if ( !$bd->updateRecordsSimple("tableau_personne",array("id" => intval($key), "tableauid" => $id),array("confirmed" => $value["bool"] == "yes" ? "t" : "f", "conftext" => $value["text"])) )
			$err++;
		
		if ( !$bd->updateRecordsSimple("tableau",array("id" => $id),array("modification" => date($config["format"]["sysdate"]." H:i:s"))) )
			$user->addAlert("Impossible de renseigner la date de mise à jour.");
		
		if ( $err > 0 )
			$user->addAlert($err." erreur(s) dans la mise à jour des confirmations");
	}
	
	// MAJ des lignes pour les commentaires / projets prioritaires
	if ( is_array($_POST["comment"]) )
	{
		$err = 0;
		foreach ( $_POST["comment"] as $key => $value )
		if ( intval($key) > 0 )
		{
			if ( !trim($value) ) $value = NULL;
			if ( !$bd->updateRecordsSimple("tableau_personne",array("id" => intval($key), "tableauid" => $id),array("comment" => $value)) )
				$err++;
		}
		if ( !$bd->updateRecordsSimple("tableau",array("id" => $id),array("modification" => date($config["format"]["sysdate"]." H:i:s"))) )
			$user->addAlert("Impossible de renseigner la date de mise à jour.");
		
		if ( $err > 0 )
			$user->addAlert($err." erreur(s) dans la mise à jour des commentaires");
	}
	
	// suppression du tableau
	if ( isset($_GET["del"]) && $id > 0 && $user->scolevel >= $config["sco"]["right"]["mod"] )
	{
		$confirm = false;
		if ( isset($_GET["confirm"]) )
		{
			// une fois la suppression confirmée
			$confirm = true;
			if ( $bd->delRecordsSimple("tableau",array("id" => $id)) )
			{
				$user->addAlert("Suppression du tableau #".$id." effectuée");
				$nav->redirect($config["website"]["base"]."sco");
			}
			else	$user->addAlert("Impossible de supprimer le tableau #".$id);
		}
		else
		{
			includePage("del");
			$bd->free();
			exit(0);
		}
	}
	
	// suppression des entrées du tableau
	if ( $user->scolevel >= $config["sco"]["right"]["mod"] )
	{
		// ligne
		if ( intval($_GET["srem"]) > 0 )
		if ( !$bd->delRecordsSimple("tableau_personne",array("id" => intval($_GET["srem"]), "transposed" => NULL)) )
			$user->addAlert("Impossible d'enlever la ligne souhaitée");
		
		// colonne
		if ( intval($_GET["mrem"]) > 0 )
		if ( !$bd->delRecordsSimple("tableau_manif",array("id" => intval($_GET["mrem"]))) )
			$user->addAlert("Impossible d'enlever la colonne souhaitée");
	}
	
	// Ajout des infos du tableau
	$err = 0;
	if ( $user->scolevel >= $config["sco"]["right"]["mod"] && is_array($_POST["client"]) )
	foreach ( $_POST["client"] as $tabpersid => $tabmanif )
	if ( intval($tabpersid) > 0 && is_array($tabmanif) )
	foreach ( $tabmanif as $tabmanifid => $tickets )
	if ( is_array($tickets) )
	{
		// on met à plat la table entry
		$arr = array();
		$arr["tabmanifid"] = intval($tabmanifid);
		$arr["tabpersid"] = intval($tabpersid);
		if ( !@$bd->addRecord("entry",$arr) )
		{
			$query	= " SELECT id FROM entry WHERE tabmanifid = ".$arr["tabmanifid"]." AND tabpersid = ".$arr["tabpersid"];
			$request = new bdRequest($bd,$query);
			if ( ($entryid = intval($request->getRecord("id"))) <= 0 )
				$entryid = 0;
			$request->free();
		}
		else	$entryid = $bd->getLastSerial("entry","id");
			
		$nbdefault = 0;		// nb de fois qu'on est passé par la case "default", pour mettre à plat la partie de la table "ticket" correspondante
		
		// entrée dans la table entry est ok, on passe au contenu
		if ( $entryid > 0 )
		foreach ( $tickets as $key => $value )
		{
			switch ( $key."" ) {
			case "2nd":
				if ( !$bd->updateRecordsSimple("entry",array("id" => $entryid),array("secondary" => $value == "yes" ? 't' : 'f')) )
					$err++;
				break;
			case "valid":
				if ( !$bd->updateRecordsSimple("entry",array("id" => $entryid),array("valid" => $value == "yes" ? 't' : 'f')) )
					$err++;
				break;
			default:
				// les tickets de chq "case"
				if ( intval($key)."" == $key."" )
				{
					// mise à plat la partie de la table "ticket" correspondante en cas de 1er passage
					if ( $nbdefault == 0 )
						$bd->delRecordsSimple("ticket",array("entryid" => $entryid));
					
					// ajout des tickets
					if ( $value != '' )
					{
						$arr = array();
						$arr["entryid"]	= $entryid;
						$resa		= preg_tarif($value);
						$arr["nb"]	= intval($resa["nb"]);
						$arr["tarifid"]	= "get_tarifid((SELECT manifid FROM tableau_manif WHERE id = ".$tabmanifid."),'".pg_escape_string($resa["tarif"])."')";
						$arr["reduc"]	= intval($resa["reduc"]);
						if ( !$bd->addRecordRaw("ticket",$arr) )
							$err++;
					}
					
					$nbdefault++;
				}
			}
		}
	}
	if ( $err > 0 ) $user->addAlert("Attention, ".$err." erreurs ont eu lieu durant le remplissage des valeurs du tableau.");
	
	// transposition de la ligne validée en billetterie classique
	if ( ($line = intval($_GET["line"])) > 0 )
	{
		if ( $transac = sco_transpose($line) )
		{
			$bd->free();
			$nav->redirect($config["website"]["base"]."evt/bill/billing.php?t=".$transac."&s=3","Redirection vers la billetterie.");
		}
		else	$user->addAlert("Impossible de transposer la ligne désirée en billetterie.");
	}
	
	// annulation d'une ligne validée en billetterie classique
	if ( ($unline = intval($_GET["untrans"])) > 0 )
	if ( sco_untranspose($unline) )
		$user->addAlert("Transposition en billetterie classique effectuée");
	else	$user->addAlert("Impossible d'annuler la ligne transposée en billetterie classique.");
	
	// si on fait un zoom sur une personne ou sur une manif
	$persid = intval($_GET["persid"]);
	$manifid = intval($_GET["manifid"]);
	
	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php includePage("actions"); ?>
<div class="body">
<h2>Tableau de saisie</h2>
<form class="entry" action="<?php echo htmlsecure($_SERVER["PHP_SELF"]).'?id='.$id.($persid > 0 ? '&persid='.$persid : '') ?>" method="post">
<script type="text/javascript"><!--
	if ( window.jQuery )
	$(document).ready(function(){
		$('a.del').click(function(){
			if ( !(lnk = $(this).attr('href')) )
				lnk = $(this).attr('lnk');
			$(this).attr('lnk',lnk).removeAttr('href');
			if ( confirm('Êtes-vous sûr de vouloir retirer cette ligne/colonne ?') )
				window.location = lnk;
		});
	});
--></script>
<div>
<?php
	// première ligne
	echo '<p>';
	
	// titre du tableau
	echo '<span class="titre">'.( $id > 0 ? "Entrée #".$id : "Nouvelle entrée" ).'</span>';
	
	// entrées existantes
	$query	= " SELECT	manif.date, manif.id AS manifid, evt.*, tab.id AS tabid,
				site.nom AS sitenom, site.id AS siteid, site.ville AS siteville
		    FROM tableau_manif AS tab, manifestation AS manif, evenement_categorie AS evt, site
		    WHERE tableauid = ".$id."
		      AND tab.manifid = manif.id
		      AND evt.id = manif.evtid
		      AND site.id = manif.siteid
		      ".($persid > 0 ? "AND tab.id IN ( SELECT tabmanifid FROM entry, ticket WHERE tabpersid = ".$persid." AND ticket.entryid = entry.id )" : "")."
		      ".($manifid > 0 ? "AND tab.id = ".$manifid : "")."
		    ORDER BY ".($order = "nom, date, sitenom, siteville");
	$request = new bdRequest($bd,$query);
	$manifs = array();
	for ( $i = 0 ; $rec = $request->getRecordNext() ; $i = abs($i-1) )
	{
		echo '<span class="manif '.($i == 0 ? 'pair' : 'impair').'">';
		echo '<a class="del" href="'.htmlsecure($_SERVER["PHP_SELF"]).'?id='.$id.'&mrem='.intval($rec["tabid"]).'"><span>retirer</span></a> ';
		echo '<a class="zoom '.($manifid > 0 ? "out" : "in").'" href="'.htmlsecure($_SERVER["PHP_SELF"]).'?id='.$id.($manifid <= 0 ? '&manifid='.intval($rec["tabid"]) : '').'"><span>zoom</span></a> ';
		echo '<a class="evt" href="evt/infos/fiche.php?id='.intval($rec["id"]).'">'.htmlsecure($rec["nom"]).'</a><span class="desc">'."Fiche de l'évènement. (ctrl+clic)".'</span> ';
		echo '<a class="date" href="evt/infos/manif.php?id='.intval($rec["manifid"]).'&evtid='.intval($rec["id"]).'">le&nbsp;'.htmlsecure($config["dates"]["dotw"][date("w",$time=strtotime($rec["date"]))]).'&nbsp;'.htmlsecure(date($config["format"]["date"].' à '.$config["format"]["maniftime"],$time)).'</a>';
		echo '<span class="desc">'."Fiche de la manifestation. (ctrl+clic)".'</span> ';
		echo '<a class="site" href="evt/infos/salle.php?id='.intval($rec["siteid"]).'">'.htmlsecure($rec["siteville"].' ('.$rec["sitenom"].')').'</a><span class="desc">'."Fiche du lieu. (ctrl+clic)".'</span> ';
		echo '</span>';
		
		$manifs[] = intval($rec["tabid"]);
		
		$nextcol = $i == 0 ? "impair" : "pair";
	}
	$request->free();
	
	// nouvelle entrée - avant-dernière colonne
	if ( $user->scolevel >= $config["sco"]["right"]["mod"] && $persid <= 0 && $manifid <= 0 )
	{
		echo '<span class="manif '.$nextcol.'">';
		echo '<input type="text" name="typemanif" value="" onkeyup="javascript: sco_newmanif(this.value,'.$id.');" />';
		echo '<span>';
		echo '<select name="newmanif[]" id="newmanif" multiple="multiple">';
		echo '<option value="">-- Manifestations --</option>';
		echo '</select>';
		echo '<input type="submit" name="manif" value="ajouter" />';
		echo '</span>';
		echo '</span>';
	}
	else	echo '<span></span>';
	
	// dernière colonne
	echo '<span class="operation"></span>';
	
	echo '</p>';
	
	// autres lignes
	$places = array();					// nb de places/tarif par manif
	
	$query	= " SELECT pers.*, tableau.id AS tabid, tableau.transposed, tableau.confirmed,
		           tableau.conftext, tableau.comment
		    FROM personne_properso AS pers, tableau_personne AS tableau
		    WHERE tableau.tableauid	= ".$id."
		      AND tableau.personneid	= pers.id
		      ".($persid > 0 ? "AND tableau.id = ".$persid : "")."
		      ".($manifid > 0 ? "AND tableau.id IN ( SELECT tabpersid FROM entry, ticket WHERE tabmanifid = ".$manifid." AND ticket.entryid = entry.id )" : "")."
		      AND (tableau.fctorgid	= pers.fctorgid OR tableau.fctorgid IS NULL AND pers.fctorgid IS NULL)
		    ORDER BY comment, nom, prenom, orgnom, fcttype, fctdesc";
	$request = new bdRequest($bd,$query);
	if ( $request->countRecords() > 0 )
		echo '<input type="hidden" name="confirmed[0]" value="" />';

	for ( $i = 0 ; $rec = $request->getRecordNext() ; $i = abs($i-1) )
	{
		// ligne déjà transposée en billetterie
		$transp = intval($rec["transposed"]) > 0 ? intval($rec["transposed"]) : false;
	
		echo '<p class="'.($i == 0 ? 'pair' : 'impair').($rec["confirmed"] == "t" ? " confirmed" : "").'">';
		
		echo '<span class="client">';
		echo $transp ? '' : '<a class="del" href="'.htmlsecure($_SERVER["PHP_SELF"]).'?id='.$id.'&srem='.intval($rec["tabid"]).'"><span>retirer</span></a> ';
		echo '<a class="zoom '.($persid > 0 ? "out" : "in").'" href="'.htmlsecure($_SERVER["PHP_SELF"]).'?id='.$id.($persid <= 0 ? '&persid='.intval($rec["tabid"]) : '').'"><span>zoom</span></a> ';
		echo '<a class="perso"	href="ann/fiche.php?id='.intval($rec["id"]).'">'.htmlsecure($rec["nom"].' '.$rec["prenom"]).'</a>';
		echo '<span class="desc">Fiche de la personne. (ctrl+clic)</span>';
		if ( intval($rec["orgid"]) > 0 )
		{
			echo ' (<a class="pro"	href="org/fiche.php?id='.intval($rec["orgid"]).'">'.htmlsecure($rec["orgnom"]).'</a>';
			echo '<span class="desc">'."Fiche de l'organisme. (ctrl+clic)".'</span>';
			echo htmlsecure($rec["fcttype"] || $rec["fctdesc"] ? ' - '.($rec["fctdesc"] ? $rec["fctdesc"] : $rec["fcttype"]) : '');
			echo ')';
		}
		
		// projet prioritaire ? commentaire.
		echo '<span class="comment">';
		echo 'Comm.: <input type="text" name="comment['.intval($rec["tabid"]).']" value="'.htmlsecure($rec["comment"]).'" />';
		echo '</span>';
		
		// transposition / confirmation
		if ( $transp > 0 )
		{
			echo '<span class="confirmed">';
			echo 'Conf.&nbsp;: ';
			echo '<input type="checkbox" name="confirmed['.intval($rec["tabid"]).'][bool]" value="yes" '.($rec["confirmed"] == 't' ? 'checked="checked"' : '').' />&nbsp;';
			echo '<input type="text" name="confirmed['.intval($rec["tabid"]).'][text]" value="'.htmlsecure($rec["conftext"]).'" />';
			echo '</span>';
		}
		
		echo '</span>';
		
		$personne = array();			// nb de places/tarif par personne
		
		// les entrées pour une manif pour un spectateur (chq cellule du coeur du tableau)
		for ( $cpt = 0 ; $cpt < count($manifs) ; $cpt++ )
		{
			$query	= " SELECT entry.*, ticket.nb, ticket.tarif, ticket.reduc
				    FROM entry, (SELECT entryid, tarif.key AS tarif, nb, reduc FROM ticket, tarif WHERE tarifid = tarif.id UNION SELECT NULL AS entryid, NULL AS tarif, NULL AS nb, NULL AS reduc) AS ticket
				    WHERE tabpersid = ".intval($rec["tabid"])."
				      AND tabmanifid = ".intval($manifs[$cpt])."
				      AND (ticket.entryid = entry.id OR ticket.entryid IS NULL)
				    ORDER BY ticket.tarif";
			$entry = new bdRequest($bd,$query);
			
			echo '<span class="clientmanif '.(intval($cpt/2) == $cpt/2 ? 'pair' : 'impair').' '.($entry->getRecord("secondary") == 't' ? 'secondchoice' : '').' '.($entry->getRecord("valid") == 't' ? 'highlight' : '').'">';
			echo '<span class="billets choice">';
			while ( $go = $entry->getRecord() )
			{
				$resa  = "";
				if ( $go["tarif"] )
				{
					$resa .= intval($go["nb"]);
					$resa .= htmlsecure($go["tarif"]);
					if ( intval($go["reduc"]) > 0 )
					$resa .= intval($go["reduc"]) < 10 ? "0".intval($go["reduc"]) : intval($go["reduc"]);
				}
				echo '<input	type="text" name="client['.intval($rec["tabid"]).']['.$manifs[$cpt].'][]" value="'.$resa.'" onchange="'."javascript: sco_newticket(this);".'"
						'.($transp ? 'disabled="disabled"' : '').' />';
				
				// on enregistre le nb de places demandées pour le tarif concerné
				if ( $go["valid"] == 't' )
					$places[$manifs[$cpt]][$go["tarif"]] += $go["nb"];
				if ( $go["valid"] == 't' )
					$personne[$go["tarif"]] += $go["nb"];
				
				if ( !$entry->nextRecord() ) break;
			}
			if ( $entry->countRecords() == 0 )
				echo '<input	type="text" name="client['.intval($rec["tabid"]).']['.$manifs[$cpt].'][]" value="" onchange="'."javascript: sco_newticket(this);".'"
						'.($transp ? 'disabled="disabled"' : '').' />';
			if ( !$go ) $go = array();
			echo '<span class="end"></span></span>';
			echo '<span class="valid choice">';
				echo '<input	type="hidden" name="client['.intval($rec["tabid"]).']['.$manifs[$cpt].'][2nd]" value="no" '.($transp ? 'disabled="disabled"' : '').' />';
				echo '<input	type="hidden" name="client['.intval($rec["tabid"]).']['.$manifs[$cpt].'][valid]" value="no" '.($transp ? 'disabled="disabled"' : '').' />';
				echo '<input	type="checkbox" name="client['.intval($rec["tabid"]).']['.$manifs[$cpt].'][2nd]" value="yes"
						onchange="javascript: sco_secondchoice(this.parentNode.parentNode,this.checked);" '.($go["secondary"] == 't' ? 'checked="checked"' : '').'
						'.($transp ? 'disabled="disabled"' : '').' /><span class="desc">2nd choix</span>';
				echo '<input	type="checkbox" name="client['.intval($rec["tabid"]).']['.$manifs[$cpt].'][valid]" value="yes"
						onchange="javascript: sco_highlight(this.parentNode.parentNode,this.checked);" '.($go["valid"] == 't' ? 'checked="checked"' : '').'
						'.($transp ? 'disabled="disabled"' : '').' /><span class="desc">Accepté</span>';
			echo '</span>';
			
			// couper/coller
			if ( !$transp )
			{
				echo '<span class="cut" title="couper" onclick="javascript: '."sco_cut(this.parentNode,'clientmanif ".(intval($cpt/2) == $cpt/2 ? "pair" : "impair")."');".'">';
					echo '<span>Couper</span>';
				echo '</span>';
				echo '<span class="paste" title="coller" onclick="javascript: '."sco_paste(this.parentNode,'clientmanif ".(intval($cpt/2) == $cpt/2 ? "pair" : "impair")."');".'">';
					echo '<span>Coller</span>';
				echo '</span>';
			}
			
			echo '</span>';
			
			$entry->free();
		}
		
		// affichage des tarifs commandés par personne
		echo '<span class="clientmanif total '.(intval($cpt/2) == $cpt/2 ? 'pair' : 'impair').'"><span class="billets choice">';
		if ( is_array($personne) )
		foreach ( $personne as $tarif => $nb )
		if ( $nb > 0 )
			echo '<input type="text" name="client['.intval($rec["tabid"]).'][all][]" value="'.htmlsecure($nb.$tarif).'" disabled="disabled" />';
		echo '</span></span>';
		
		// dernière colonne, passage à la véritable billetterie
		if ( $transp || $config["sco"]["sql"]["trinentries"] != "false" )
		{
			echo '<span class="operation" onmouseout="javascript: '." e=this.getElementsByTagName('a'); if ( e.length > 0 ) if (e.item(0).href == '".$url."') e.item(0).href='evt/bill/billing.php'; e.item(0).className='';".'">';
			echo '<a href="'.($transp ? $config["website"]["base"].'evt/bill/billing.php?t='.$transp.'&s=3' : $url = htmlsecure($config["website"]["base"].'sco/fiche.php').'?id='.$id.'&line='.intval($rec["tabid"])).'"
				onmouseup="javascript: '."sco_disableinputs(this.parentNode.parentNode); this.className='hidden';".'">';
			echo '&gt;&gt;</a>';
			echo '<span class="desc">'."Pour la transposition vers la billetterie, l'utilisation d'onglets est conseillée... (ctrl+clic)".'</span>';
		}
		if ($transp)
		{
			echo '<span class="newline"></span>';
			echo '<a class="untrans" href="'.htmlsecure($_SERVER["PHP_SELF"]).'?id='.$id.'&untrans='.intval($rec["tabid"]).'">';
			echo '&lt;&lt;</a>';
			echo '<span class="desc">Annule la transposition uniquement sur le module "scolaires et groupes". '."Attention, l'opération est toujours active en billetterie.</span>";
		}
		echo '</span>';
		
		echo '</p>';
		
		$nextline = $i == 0 ? "impair" : "pair";
	}
	$request->free();
	
	// nouveau client - avant-dernière ligne
	echo '<p class="'.$nextline.'">';
	if ( $user->scolevel >= $config["sco"]["right"]["mod"] && $persid <= 0 && $manifid <= 0 )
	{
		echo '<span class="client">';
		echo '<input type="text" name="typeclient" value="" onkeyup="javascript: sco_annu(this.value);" />';
		echo '<span>';
		echo '<select name="newclient[]" id="newclient" multiple="multiple">';
		echo '<option value="">-- Spectateurs --</option>';
		echo '</select>';
		echo '<input type="submit" name="manif" value="ajouter" />';
		echo '</span>';
		echo '</span>';
	}
	else	echo '<span></span>';
	
	// les jauges
	if ( $manifs )
	{
		$query	= " SELECT tabmanif.id AS tabmanifid, jauge,
			         ( SELECT sum(- (resa.annul::integer * 2 - 1)) AS sum
			           FROM reservation_pre resa
		                   WHERE resa.manifid = manif.id
		                     AND resa.transaction NOT IN ( SELECT transposed FROM tableau_personne AS tabpers WHERE tabmanif.tableauid = tabpers.tableauid AND transposed IS NOT NULL )
		                     AND (resa.id IN ( SELECT reservation_cur.resa_preid
		                                       FROM reservation_cur
		                                       WHERE reservation_cur.canceled = false))) AS resas,
		                 ( SELECT sum(- (resa.annul::integer * 2 - 1)) AS sum
		                   FROM reservation_pre resa
		                   WHERE resa.manifid = manif.id
		                     AND resa.transaction NOT IN ( SELECT transposed FROM tableau_personne AS tabpers WHERE tabmanif.tableauid = tabpers.tableauid AND transposed IS NOT NULL )
		                     AND NOT (resa.id IN ( SELECT reservation_cur.resa_preid
		                                           FROM reservation_cur
		                                           WHERE reservation_cur.canceled = false))
		                     AND resa.transaction IN ( SELECT preselled.transaction FROM preselled )
		                     AND resa.transaction NOT IN ( SELECT transaction FROM contingeant WHERE fctorgid IN (SELECT fctorgid FROM responsable) ) ) AS preresas,
		                 ( SELECT sum(ticket.nb) AS nb
		                   FROM ticket, entry, tableau_manif AS tabmanif
		                   WHERE ticket.entryid = entry.id
		                     AND tabmanif.id = entry.tabmanifid
		                     AND tabmanif.manifid = manif.id
		                     AND entry.valid ) AS accepted,
		                 site.nom AS sitenom, site.ville AS siteville, evt.nom, tabmanif.id AS tabid
			    FROM manifestation AS manif, tableau_manif AS tabmanif, evenement AS evt, site
			    WHERE tabmanif.id IN (".implode(",",$manifs).")
			      AND manif.id = tabmanif.manifid
			      AND manif.evtid = evt.id
			      AND site.id = manif.siteid
			    ORDER BY ".$order;	// attention à l'ordre. il doit être le même que pour l'affichage des manifs.
		$request = new bdRequest($bd,$query);
		
		// affichage de la jauge
		for ( $cpt = 0 ; $rec = $request->getRecordNext() ; $cpt++ )
		if ( intval($rec["tabid"]) == $manifs[$cpt] && is_array($places) )
		{
			echo '<span class="jauge new">';
			printJauge(intval($rec["jauge"]),intval($rec["accepted"]),intval($rec["preresas"])+intval($rec["resas"]),60);
			echo '</span>';
		}
	
		$request->free();
		echo '<span class="clientmanif new"></span>';
		echo '</p>';
		
		// si on a fait une ligne de jauge, on en commence une autre
		echo '<p class="'.$nextline.'">';
		echo '<span class="new"></span>';
	}
	
	// dernière ligne
	$personne = array();
	for ( $cpt = 0 ; $cpt < count($manifs) ; $cpt++ )
	{
		echo '<span class="clientmanif new"><span class="billets choice">';
		if ( is_array($places[$manifs[$cpt]]) )
		foreach ( $places[$manifs[$cpt]] as $tarif => $nb )
		if ( $nb > 0 )
		{
			echo '<input type="text" name="client[new]['.$manifs[$cpt].'][]" value="'.htmlsecure($nb.$tarif).'" disabled="disabled" />';
			$personne[$tarif] += $nb;
		}
		echo '</span></span>';
	}

	// affichage des tarifs commandés au total
	echo '<span class="clientmanif new"><span class="billets choice">';
	if ( is_array($personne) )
	foreach ( $personne as $tarif => $nb )
	if ( $nb > 0 )
		echo '<input type="text" name="client['.intval($rec["tabid"]).'][all][]" value="'.htmlsecure($nb.$tarif).'" disabled="disabled" />';
	echo '</span></span>';
	
	// dernière colonne
	echo '<span class="operation"></span>';

	echo '</p>';
?>
</div>
<p class="submit"><input type="submit" name="submit" value="Enregistrer" /></p>
</form>
<div class="reminder">
	<p class="jauge">
		<span class="title">Jauge:</span>
		<span class="green">places libres</span>
		<span class="orange">places prises dans le module scolaire</span>
		<span class="red">places prises en billetterie</span>
	</p>
	
</div>
</div>
<?php
	includeLib("footer");
?>
