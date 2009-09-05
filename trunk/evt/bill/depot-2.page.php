<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
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
	global $bd,$user,$nav,$class,$data,$title,$stage,$oldtransac,$subtitle,$sqlcount,$css,$jauge;
	
	includeClass("reservations");
	includeLib("jauge");
	includeJS("ajax");
	includeJS("bill","evt");
	
	// l'impression des billets
	$print = $stage == 3;
		
	includeLib("headers");
	$jauge = true;
	$action = $actions["add"];
	$resumeClass = $stage == 4 ? "resume done" : "resume";
	
	// init tarifs
	$query	= "SELECT *
		   FROM tarif";
	$request = new bdRequest($bd,$query);
	while ( $tarif = $request->getRecordNext() )
		$tarifs[$tarif["key"]] = $tarif["description"];
	$request->free();
	
	// récup du tarif correspondant aux places contingeantées
	$pc = array();
	$part = array();
	if ( is_array($data["manif"]) && count($data["manif"]) > 0 )
	{
		foreach ( $data["manif"] as $value )
		{
			// permet de gagner bcp de temps
			$query = " SELECT get_tarifid_contingeant(".$value.") AS tarifid";
			$request = new bdRequest($bd,$query);
			$buf = intval($request->getRecord("tarifid"));
			$request->free();
			
			$part[]	= " SELECT ".$value." AS manifid, tarif.key
				    FROM tarif
				    WHERE tarif.id = ".$buf;
		}
		$query = implode(" UNION ",$part);
		$contid = new bdRequest($bd,$query);
		while (	$rec = $contid->getRecordNext() )
			$pc[intval($rec["manifid"])] = $rec["key"];
		$contid->free();
	}
	
	// traitement des commandes
	if ( substr($data["client"],0,4) == "pers" )
	{
		$clientid = intval(substr($data["client"],5));
		$proid = NULL;
		$query = "SELECT id, titre, nom, prenom FROM personne_properso WHERE id = ".$clientid." AND fctorgid IS NULL";
		$request = new bdRequest($bd,$query);
		$perso  = $request->getRecord("titre")." ".$request->getRecord("nom")." ".$request->getRecord("prenom");
		$request->free();
	}
	else
	{
		$proid = intval(substr($data["client"],5));
		$query = " SELECT id, titre, nom, prenom FROM personne_properso WHERE fctorgid = ".$proid;
		$request = new bdRequest($bd,$query);
		$perso  = $request->getRecord("titre")." ".$request->getRecord("nom")." ".$request->getRecord("prenom");
		$clientid = intval($request->getRecord("id"));
		$request->free();
	}
	
	// blocage automatique des places réservées ici dans la table contingeant si pas déjà fait
	$request = new bdRequest($bd,"SELECT contingeanting(".pg_escape_string($data["numtransac"]).",".$user->getId().",".$clientid.",".($proid ? $proid : "NULL").")");
	$request->free();
	
	$places = array();
	$resa = new reservations($bd,$user,$data["numtransac"],$clientid,$proid);
	$resa->updateTransaction();
	
	$bd->beginTransaction();
	
	// nettoyage des pré-resas en base (logique, mais bizarre qd mm de le faire)
	if ( $stage == 2 && !$oldtransac )
	if ( $bd->delRecords("reservation_pre","transaction = '".pg_escape_string($data["numtransac"])."' AND id NOT IN (SELECT resa_preid FROM reservation_cur)") === false )
		$user->addAlert("Erreur dans la mise à jour des pré-réservations");
	// nettoyage des billets édités en masse en base
	//if ( $stage == 3 && !$oldtransac && isset($_POST["stage3"]) )
	//if ( !$bd->delRecordsSimple("masstickets",array("transaction" => $data["numtransac"])) )
	//	$user->addAlert("Erreur dans la mise à jour des billets imprimés en masse");
	
	if ( is_array($data["billet"]) && !$oldtransac )
	foreach ( $data["billet"] as $manifid => $billet )
	{
		$places[intval($manifid)]	= array();
		if ( is_array($billet) )
		foreach ( $billet as $value )
		{
			// reformatage en cas de stage3 tout nouveau : rajouter "PC00" (par ex)
			// en fin de $value pour compenser le tarif pré-rempli
			if ( !isset($_POST["stage3"]) )
				$bill = intval($value).$pc[intval($manifid)]."00";
			
			// récup des données
			$arr = preg_tarif(strtoupper($bill));
			
			// renseignement des pré-resas
			if ( $stage == 2 && $arr["tarif"] && $arr["nb"] > 0 && intval($value) > 0 )
				$resa->addPreReservation(intval($manifid),$arr);
			
			// étranger d'avoir ça là...
			$bd->endTransaction();
			
			$arr = preg_tarif(strtoupper($value));
			
			// renseignement des masstickets
			if ( $stage == 3 && $arr["tarif"] && isset($_POST["stage3"]) )
			{
				$arr["tarifid"]		= "get_tarifid(".intval($manifid).",'".pg_escape_string($arr["tarif"])."')";
				$arr["accountid"]	= $user->getId();
				$arr["transaction"]	= "'".pg_escape_string($data["numtransac"])."'";
				$arr["manifid"]		= intval($manifid);
				$arr["nb_orig"]		= $arr["nb"];
				
				$full = $arr["full"];
				unset($arr["full"]);
				unset($arr["tarif"]);
				unset($arr["other"]);
				
				if ( !@$bd->addRecordRaw("masstickets",$arr) )
				{
					$cond = "    transaction = '".pg_escape_string($data["numtransac"])."'
						 AND tarifid = ".$arr["tarifid"]."
						 AND reduc = ".$arr["reduc"]."
						 AND printed <= 0
						 AND manifid = ".intval($manifid);
					$changes = array("nb" => "nb + ".$arr["nb"], "nb_orig" => "nb + ".$arr["nb"]);
					if ( !$bd->updateRecordsRaw("masstickets",$cond,$changes) )
						$user->addAlert("Impossible d'ajouter les places ".htmlsecure($full)." !");
				}
			}
			unset($arr);
		}
	} // foreach ( $data["billet"] as $manifid => $billet )
	
	$bd->endTransaction();
	
	// Récup des pré-réservation
	if ( $stage < 4 )
		$places = $resa->getPreReservations();

	if ( $stage == 4 )
		$places = $resa->getReservations();
?>
<script type="text/javascript">
	var resumeClass = '<?php echo $resumeClass ?>';
</script>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
<?php
	if ( $subtitle ) echo '<h2>'.htmlsecure($subtitle).'</h2>';
	includePage("depot-stages");
?>
<p class="numtransac">
	<span><?php echo htmlsecure("Spectateur: ".$perso) ?></span>
	;
	<span>Numéro d'opération:</span> <span>#<?php echo htmlsecure($data["numtransac"]) ?></span>
</p>
<form	name="formu" class="<?php if ( $print ) echo 'print ' ?>resa" method="post"
	action="<?php echo $_SERVER["PHP_SELF"] ?>">
	<div class="clip">
	<div class="manifestations">
	<fieldset class="hidden">
		<?php printHiddenFields($data) ?>
		<?php if ( $stage == 3 ) { ?><input type="hidden" name="stage3" value="" /><?php } ?>
	</fieldset>
	<?php
		// récup des prix
		global $prices;

		if ( $stage > 2 )
		{
			$query = " CREATE TEMP TABLE tmptickets AS
				   SELECT masstickets.*, tarif.key AS tarif
				   FROM masstickets, tarif
				   WHERE transaction = '".pg_escape_string($data["numtransac"])."'
				     AND tarif.id = tarifid";
			$request = new bdRequest($bd,$query);
			$request->free();
		}
		
		$query	= " SELECT evt.id AS id, manif.id AS manifid, evt.nom, site.nom AS sitenom, site.ville,
			           manif.date, evt.categorie, evt.catdesc, colors.libelle AS colorname
			    FROM evenement_categorie AS evt, manifestation AS manif, site, colors ";
		$arr = $stage == 2 ? $data["manif"] : array_keys($places);
		if ( count($arr) > 0 )
			$query .= "WHERE manif.id IN (".implode(",",$arr).")";
		else	$query .= "WHERE manif.id IS NULL";
		$query .= "   AND ( colors.id = manif.colorid OR manif.colorid IS NULL AND colors.id IS NULL )
			      AND evt.id = manif.evtid
			      AND site.id = manif.siteid
			    ORDER BY nom, date";
		
		$request = new bdRequest($bd,$query);
		for ( $i = 0 ; $rec = $request->getRecordNext() ; $i++ )
		{
			echo '<p class="content" onmouseover="javascript: bill_jauge('.intval($rec["manifid"]).');">';
			printManif($rec);
			echo '<span class="edit '.($stage == 2 ? 'pc' : '').'"><input type="text" tabindex="'.($i+1).'" class="number" name="billet['.$rec["manifid"].'][]" value="" size="5" maxlength="10" '.($i==0 ? 'id="focus"' : '').'/>'.($stage == 2 ? " ".$pc[intval($rec["manifid"])]." 00" : "").'</span>';
			
			// Affichage des billets à imprimer pour le dépôt de billeterie
			if ( $stage > 2 )
			{
				$key = 0;
				$query = " SELECT * FROM tmptickets WHERE manifid = ".intval($rec["manifid"]);
				$masstickets = new bdRequest($bd,$query);
				while ( $tick = $masstickets->getRecordNext() )
				if ( $tick["nb"] != 0 )
				{
					$resa = array();
					$resa[$name = "nb"]	= $tick[$name];
					$resa[$name = "tarif"]	= $tick[$name];
					$resa[$name = "reduc"]	= intval($tick[$name]) < 10 ? "0".intval($tick[$name]) : intval($tick[$name]);
					$resa[$name = "full"]	= $resa["nb"].$resa["tarif"].$resa["reduc"];
					$resa[$name = "manifid"]= $tick[$name];
					$resa[$name = "printed"]= intval($tick[$name]) > 0;
					
					
					$resumeClassTmp = $resumeClass;
					
					echo '<span class="'.$resumeClassTmp.($resa["printed"] ? " done" : "").'" id="billets'.$rec["manifid"].'.'.$key.'" ';
					if ( $stage < 3 ) echo 'onclick="javascript: this.parentNode.removeChild(this)"';
					echo '>';
					
					echo '<span class="billet">';
					echo htmlsecure($resa["nb"].' '.$resa["tarif"].' '.$resa["reduc"]);
					echo '<input type="hidden" name="bill['.intval($resa["manifid"]).'][]" value="'.htmlsecure($resa["full"]).'" />';
					echo '</span>';
					
					// Affichage des résultats liés aux impressions
					if ( $stage < 3 ) echo '<span class="desc">cliquer sur le billet pour les supprimer</span>';
					if ( $stage == 3 )
					{
						echo '<span class="desc imp">'."une fois imprimés, leur nombre est figé. La solution: créer un autre dépôt.".'</span>';
						echo '<span class="desc">'."une fois imprimés, leur nombre est figé.".'</span>';
					}
					
					echo '</span>';
					
					// Affichage des options d'impression
					if ( $print )
					{
						echo '<span class="valid"><span><span class="visu"></span>';
						echo '<input	type="button" name="print"
								onclick="javascript: '."printDepot(this,".intval($rec["manifid"]).",'".$resa["full"]."',".$key.",".$data["numtransac"].")".';"
								value="Imprimer"/>';
						echo '</span></span>';
					}
					
					// pour l'id des billets
					$key++;
				}
				$masstickets->free();
			}
			
			// affichage des places contingeantées
			if ( is_array($places[$rec["manifid"]]) )
			foreach ( $places[$rec["manifid"]] as $key => $resa )
			if ( $resa["nb"] != 0 && $resa["printed"] != 't' )
			{
				$resumeClassTmp = $resumeClass." contingeant";
				
				echo '<span class="'.$resumeClassTmp.'" id="billets'.$rec["manifid"].'.'.intval($key).'" ';
				echo $stage == 2 ? 'onclick="javascript: this.parentNode.removeChild(this)"' : "";
				echo '>';
				
				if ( $stage < 3 ) echo '<input type="hidden" name="billet['.intval($rec["manifid"]).'][]" value="'.htmlsecure($resa["full"]).'" />';
				echo '<span class="billet">';
				echo htmlsecure($resa["nb"].' '.$resa["tarif"].' '.$resa["reduc"]);
				echo '</span>';
				
				// Affichage des résultats liés aux impressions
				if ( $stage == 2 )
					echo '<span class="desc">cliquer sur la réservation pour la supprimer</span>';
				else	echo '<span class="desc">place(s) contingeantée(s)</span>';
				
				echo '</span>';
			}
			
			echo '</p>';
			if ( $jauge )
			{
				echo '<p class="content jauge" onclick="javascript: '."ttt_spanCheckBox(this.parentNode.getElementsByTagName('input').item(0))".';">';
				echo '<span id="manif_'.intval($rec["manifid"]).'">';
				//printJauge(intval($rec["jauge"]),intval($rec["preresas"]),intval($rec["resas"]),450,intval($rec["commandes"]),550,$user);
				echo '</span></p>';
			}
		}
		$request->free();
	?>
	</div>
	</div>
	<?php if ( $stage == 4 ) { ?>
	<div class="reglement"><?php includePage("grp-reglement"); ?></div>
	<?php } if ( $stage < 4 ) { ?>
	<div class="valid">
		<p class="submit"><input type="submit" name="<?php echo $stage < 3 ? 'add' : 'filled' ?>" value="Valider" /></p>
	</div>
	<?php } ?>
	<p class="submit">
		<input	type="button"
			onclick="javascript: location.replace('<?php echo htmlsecure($_SERVER["PHP_SELF"].'?t='.$data["numtransac"]) ?>');"
			class="back" value="&lt;&lt; Départ" name="back" />
		<input type="submit" class="next" value="Suivant &gt;&gt;" name="<?php if ( $print ) echo "printed"; else echo $stage == 2 ? "filled" : "paid" ?>" />
	</p>
<?php
	includePage("manifs");
?>
	<div class="reminder">
		<?php includePage("reminder"); ?>
	</div>
</form>
</div>
<?php
	$bd->free();
 	includeLib("footer");
?>
