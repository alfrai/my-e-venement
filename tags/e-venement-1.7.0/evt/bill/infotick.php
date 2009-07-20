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
	$class .= " infotick";
	
	if ( $user->evtlevel < $config["evt"]["right"]["mod"] )
	{
		$user->addAlert($msg = "Vous n'avez pas un niveau de droits suffisant pour accéder à cette fonctionnalité");
		$nav->redirect($config["website"]["base"]."evt/bill/",$msg);
	}
	
	if ( !$config["ticket"]["dematerialized"] )
	{
		$user->addAlert("Billets dématérialisés indisponibles");
		$nav->redirect($config["website"]["base"]."evt/bill");
	}
	
	includeJS("ajax");
	includeJS("annu");
	
	$stage = 1;
	
	// refusé au dernier moment...
	if ( isset($_POST["invalid"]) )
		unset($_POST);
	
	// prédéfinition de la manifestation sélectionnée
	if ( intval($_POST["manif"]) > 0 )
		$user->dematmanif = intval($_POST["manif"]);
	elseif ( intval($_POST["manif"]) < 0 )
		unset($user->dematmanif);
	if ( intval($user->dematmanif) > 0 )
		$stage = 2;
	
	// arrivée au stage 3 par le numéro de transaction ou la personne
	if ( $stage == 2 && ( intval($_POST["t"]) || intval($_POST["pers"]) ) )
	{
		$query	= " SELECT transaction.personneid, transaction, count(*) AS nb, plnum
			    FROM reservation_pre AS preresa, reservation_cur AS resa, transaction
			    WHERE manifid = ".intval($user->dematmanif)."
			      AND resa.resa_preid = preresa.id
			      AND NOT canceled
			      AND NOT annul
			      AND dematerialized
			      AND NOT dematerialized_passed
			      AND transaction.id = transaction";
		if ( intval($_POST["t"]) )
		$query .= "   AND transaction = ".intval($_POST["t"]);
		if ( intval($_POST["pers"]) )
		$query .= "   AND personneid = ".intval($_POST["pers"]);
		$query .= " GROUP BY personneid, transaction, plnum";
		$stage3 = new bdRequest($bd,$query);
		if ( $stage3->countRecords() > 0 )
		{
			$stage = 3;
			$personneid = intval($stage3->getRecord("personneid"));
			$numtransac = intval($stage3->getRecord("transaction"));
			
			$nbplaces = 0;
			while ( $rec = $stage3->getRecordNext() )
				$nbplaces += intval($rec["nb"]);
		}
		else	$user->addAlert("Données envoyées insolvables... ou entrée déjà effectuée.");
	}
	
	// validation au niveau du stage 3
	if ( $stage == 3 && $numtransac && $personneid && isset($_POST["valid"]) )
	{
		// on passe alors au stage 4
		$stage = 4;
		$sysvalid = false;
		
		// MAJ pour indiquer le passage du spectateur en caisse
		$condition = array(	"transaction" => $numtransac,
					"manifid" => $user->dematmanif,
					"dematerialized_passed" => "f");
		if ( $nbplaces == $bd->updateRecordsSimple("reservation_pre",$condition,array("dematerialized_passed" => "t")) )
			$sysvalid = true;
	}
	
	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
<h2>Billets dématérialisés - Infoticks</h2>
<?php includePage("infotick-stages"); ?>
<form action="<?php echo htmlsecure($_SERVER["PHP_SELF"]); ?>" method="post">
<?php
	if ( $stage == 4 )
	{
		if ( $sysvalid )
		{
			$query = " SELECT * FROM personne WHERE id = ".$personneid;
			$request = new bdRequest($bd,$query);
			$rec = $request->getRecord();
			echo '<p class="passage ok">Merci '.htmlsecure($rec["titre"].' '.$rec["prenom"].' '.$rec["nom"]).'<a href="ann/fiche.php?id='.$rec["id"].'&view"> </a>, et bon spectacle !</p>';
			$request->free();
		}
		else
		{
			$msg = "Passage refusé !!";
			$user->addAlert($msg);
			echo '<p class="passage ko">'.htmlsecure($msg).'</p>';
		}
	}
	else
	{
?>
	<p>
		<span>Manifestation en cours&nbsp;:</span>
		<select name="manif" id="manif" onchange="javascript: document.getElementById('nummanif').value=this.value"><?php
			if ( !$personneid ) echo '<option value="-1">-- Choisir --</option>';
			$query	= " SELECT evt.nom, site.nom AS site, manif.date, manif.id, evt.id AS evtid
				    FROM manifestation AS manif, evenement AS evt, site
				    WHERE manif.evtid = evt.id
				      AND manif.siteid = site.id";
			$query .= $personneid ? " AND manif.id = ".$user->dematmanif : "";
			$query .= " ORDER BY evt.nom, site.nom, manif.date";
			$request = new bdRequest($bd,$query);
			$evt = 0;
			while ( $rec = $request->getRecordNext() )
			{
				if ( $evt != intval($rec["evtid"]) )
				{
					if ( $evt != 0 ) echo '</optgroup>';
					$evt = intval($rec["evtid"]);
					echo '<optgroup label="'.htmlsecure($rec["nom"]).'">';
				}
				echo '<option value="'.intval($rec["id"]).'" '.($user->dematmanif == intval($rec["id"]) ? 'selected="selected"' : '').'>';
				echo htmlsecure($rec["nom"].' - '.date($config["format"]["date"].' '.$config["format"]["maniftime"],strtotime($rec["date"])).' - '.$rec["site"]);
				echo '</option>';
			}
			if ( $evt != 0 ) echo '</optgroup>';
			$request->free();
		?></select>
		(numéro #<input type="text" id="nummanif" name="nummanif" value="<?php echo htmlsecure($user->dematmanif > 0 ? $user->dematmanif : '') ?>" size="5" onchange="javascript: document.getElementById('manif').value=this.value;" <?php echo $personneid ? 'disabled="disabled"' : '' ?>/>)
		<?php
			if ( $user->dematmanif )
				echo ' - <a title="Extraire le listing papier des billets dématérialisés pour cette manifestation" href="evt/bill/infotick-listing.php?manif='.$user->dematmanif.'">listing...</a>';
		?>
	</p>
	<?php if ( intval($user->dematmanif) > 0 ) { ?>
	<p>
		<span>Numéro de billet dématérialisé&nbsp;:</span>
		<span>#<input type="text" size="5" value="<?php if ( $numtransac ) echo $numtransac ?>" name="t" id="focus" <?php if ( $personneid ) echo 'disabled="disabled"' ?> /></span>
	</p>
	<p>
		<span>Nom du spectateur&nbsp;:</span>
		<span><select id="pers" <?php if ( $personneid ) echo 'disabled="disabled"' ?> name="pers" onchange="javascript: if ( this.value ) annu_persmicrofiche(this.value);"><?php
			echo '<option value=""></option>';
			$query	= " SELECT personne.id, personne.nom, personne.prenom, transaction, count(*) AS nbplaces
				    FROM reservation_pre AS preresa, reservation_cur AS resa, personne, transaction
				    WHERE resa.resa_preid = preresa.id
				      AND NOT resa.canceled
				      AND NOT annul
				      AND dematerialized
				      AND transaction.id = preresa.transaction
				      AND personne.id = transaction.personneid
				      AND preresa.manifid = ".intval($user->dematmanif)."
				    GROUP BY personne.id, nom, prenom, transaction
				    ORDER BY nom, prenom, transaction";
			$request = new bdRequest($bd,$query);
			while ( $rec = $request->getRecordNext() )
				echo '<option value="'.intval($rec["id"]).'" '.(intval($rec["id"]) == $personneid ? 'selected="selected"' : '').'>'.htmlsecure($rec["nom"].' '.$rec["prenom"].' ('.$rec["nbplaces"]." pl.)").'</option>';
			$request->free();
		?></select></span>
	</p>
	<?php
		if ( $personneid && $numtransac )
		{
			echo '<input type="hidden" name="manif" value="'.$user->dematmanif.'" />';
			echo '<input type="hidden" name="t" value="'.$numtransac.'" />';
			echo '<input type="hidden" name="pers" value="'.$personneid.'" />';
		}
		
		if ( $nbplaces )
		{
			echo '<p id="nbplaces">';
			echo htmlsecure($nbplaces." entrées - ");
			$stage3->firstRecord();
			$arr = array();
			while ( $rec = $stage3->getRecordNext() )
			{
				if ( is_null($rec["plnum"]) )
					$arr[] = intval($rec["nb"])." place(s) libre(s)";
				else	$arr[] = 'num.'.$rec["plnum"];
			}
			echo implode(",",$arr);
		}
	}
	
	} // else ( $stage == 4 )
	
	// passages aux étapes suivantes
	echo '<p';
	if ( $stage < 3 )
		echo '><input type="submit" name="submit" value="Suivant &gt;&gt;" />';
	if ( $stage > 3 )
		echo ' class="back">&lt;&lt; <a href="'.htmlsecure($_SERVER["PHP_SELF"]).'">Nouvelle vérification</a> de billet dématérialisé';
	if ( $stage == 3 )
	{
		echo '><input type="submit" name="invalid" value="&lt;&lt; Refuser" /> ';
		echo '<input type="submit" name="valid" value="Valider &gt;&gt;" />';
	}
	echo '</p>';
?>
</form>
<div id="ficheindiv"></div>
<?php	if ( $personneid ) { ?>
	<script type="text/javascript">
		var elt = document.getElementById("pers");
		if ( elt )
		if ( elt.value )
			annu_persmicrofiche(elt.value);
	</script>
<?php } // if ( $personneid ) ?>
</div>
<?php
	if ( $stage >= 3 ) $stage3->free();
	includeLib("footer");
	$bd->free();
?>
