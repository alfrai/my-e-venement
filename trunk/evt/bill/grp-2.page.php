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
	global $bd,$user,$nav,$class,$data,$title,$stage,$oldtransac,$subtitle,$sqlcount,$prices,$css,$jauge;

	includeClass("reservations");
	includeLib("jauge");
	includeLib("plnum");
	includeJS("plnum","evt");
	includeJS("ajax");
	includeJS("bill","evt");
	
	// l'impression des billets
	$print = $stage == 3;
	
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
	
	// traitement des commandes
	if ( substr($data["client"],0,4) == "pers" )
	{
		$clientid = intval(substr($data["client"],5));
		$proid = NULL;
		$query = "SELECT id, titre, nom, prenom FROM personne_properso WHERE id = ".$clientid." AND fctorgid IS NULL";
		$request = new bdRequest($bd,$query);
		$perso	= $request->getRecord("titre")." ".$request->getRecord("nom")." ".$request->getRecord("prenom");
		$request->free();
	}
	else
	{
		$proid = intval(substr($data["client"],5));
		$query = " SELECT id, titre, nom, prenom FROM personne_properso WHERE fctorgid = ".$proid;
		$request = new bdRequest($bd,$query);
		$perso	= $request->getRecord("titre")." ".$request->getRecord("nom")." ".$request->getRecord("prenom");
		$clientid = intval($request->getRecord("id"));
		$request->free();
	}
	
	$places = array();
	$resa = new reservations($bd,$user,$data["numtransac"],$clientid,$proid);
	
	$bd->beginTransaction();
	
	// nettoyage des pré-resas en base
	if ( $stage == 2 && !$oldtransac && $data["numtransac"] )
	if ( $bd->delRecordsSimple("reservation_pre",array("transaction" => $data["numtransac"], "plnum" => NULL)) === false )
		$user->addAlert("Erreur dans le nettoyage des pré-réservations");
	
	if ( is_array($data["billets"] = $_POST["billet"]) && !$oldtransac )
	foreach ( $data["billets"] as $manifid => $billet )
	{
		$places[intval($manifid)]	= array();
		if ( is_array($billet) )
		foreach ( $billet as $value )
		{
			// récup des données
			$arr = preg_tarif(strtoupper($value));
			
			// renseignement des pré-resas
			if ( $stage == 2 || strstr($_SERVER["HTTP_REFERER"],$config["website"]["base"]."evt/bill/annul.php") )
			{
				//$resa->addPreReservation(intval($manifid),$arr);
				
				// nouvelle méthode pour places numérotées
				$preresa = array();
				$preresa['accountid']   = $user->getId();
				$preresa['manifid']     = $manifid;
				$preresa['tarifid']     = "(SELECT get_tarifid(".$manifid.",'".$arr['tarif']."'))";
				$preresa['reduc']       = intval($arr['reduc']);
				$preresa['annul']       = intval($arr['nb']) < 0 ? "'t'" : "'f'";
				
				// méthode grossière pour récup le numéro de la place
				$tmp = spliti("plnum-",$arr["other"]);
				if ( ($preresa['plnum'] = intval($tmp[1])) <= 0 )
				if ( ($preresa['plnum'] = intval($arr["other"])) <= 0 )
				unset($preresa['plnum']);
				
				$preresa['transaction'] = "'".pg_escape_string($data['numtransac'])."'";
				$add = 0;
				for ( $i = 0 ; $i < abs(intval($arr['nb'])) ; $i++ )
				  $add += $bd->addRecordRaw("reservation_pre",$preresa) ? 1 : 0;
				
		    $prices = getPrices($data["numtransac"]);
				
				if ( $add = 0 )
				$user->addAlert("Aucune demande n'a été enregistrée pour la manifestation #".$manifid);
			}
			
			// reseignement des places réservées à annuler si le contexte est bon (attention aux reloads)
			if ( $config["ticket"]["placement"]
			  && intval($arr["other"])."" == $arr["other"].""
			  && strstr($_SERVER["HTTP_REFERER"],$config["website"]["base"]."evt/bill/annul.php") )
			{
				$plnum[intval($manifid)][$arr["tarif"].$arr["reduc"]][] = $arr["other"];
			}
			
			unset($arr);
		}
	} // foreach ( $data["billets"] as $manifid => $billet )
	
	$bd->endTransaction();
	
	// Récup des pré-réservation
	if ( $stage < 4 )
		$places = $resa->getPreReservations();
		
	if ( $stage == 4 )
		$places = $resa->getReservations();
	
	if ( $stage == 2 )
		$prices = getPrices($data["numtransac"]);
	
	includeLib("headers");
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
	includePage("grp-stages");
?>

<p class="numtransac">
	<span class="client"><?php echo htmlsecure("Spectateur: ".$perso) ?></span>
	;
	<span class="operation">Numéro d'opération:</span> <span>#<?php echo htmlsecure($data["numtransac"]) ?></span>
	<?php
		// transactions liées
		$query	= " SELECT transaction.id
			    FROM transaction, reservation_pre AS pre, reservation_cur AS cur
			    WHERE translinked = ".$data["numtransac"]."
			      AND pre.transaction = transaction.id
			      AND cur.resa_preid = pre.id
			      AND NOT canceled
			    ORDER BY id";
		$links = new bdRequest($bd,$query);
		
		if ( $links->countRecords() > 0 )
		{
			echo '<span class="links">(liée avec: ';
			$past = array();
			for ( $i = 0 ; ($buf = intval($links->getRecordNext("id"))) > 0 ; $i++ )
			{
				if ( !in_array($buf,$past) )
				{
					$past[] = $buf;
					if ( $i > 0 ) echo ', ';
					echo '#<a href="'.htmlsecure($_SERVER["PHP_SELF"]).'?t='.htmlsecure($buf).'">'.htmlsecure($buf).'</a>';
				}
			}
			echo ')</span>';
		}
		
		$request->free();
	?>
</p>
<form	name="formu" class="<?php if ( $print ) echo 'print ' ?>resa" method="post"
	<?php if($stage==3) { ?>onsubmit="javascript: cleanNonValidated(this);"<?php } ?>
	action="<?php echo $_SERVER["PHP_SELF"] ?>">
	<div class="clip">
	<div class="manifestations billets">
	<fieldset class="hidden">
		<?php printHiddenFields($data) ?>
	</fieldset>
	<?php
		// récup des prix
		global $prices;

		$query	= " SELECT evt.id AS id, manif.id AS manifid, evt.nom,
			           site.id AS siteid, site.nom AS sitenom, site.ville,
			           manif.date, evt.categorie, evt.catdesc, colors.libelle AS colorname,
			           plnum
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
			
			// saisie d'un nouveau lot de billets
			if ( $stage == 2 )
			{
				// si places numérotées
				if ( $rec["plnum"] == 't' )
					echo '<input	type="button" class="plan"
							onclick="javascript: '.
								"this.form.action = 'evt/bill/salle.php?manifid=".intval($rec["manifid"])."';
								 this.form.submit();".'" name="plan" value="plan" />';
				// placement libre
				else	echo '<span class="edit"><input type="text" tabindex="'.($i+1).'" class="number" name="billet['.$rec["manifid"].'][]" value="" size="5" maxlength="10" '.($i==0 ? 'id="focus"' : '').'/></span>';
			}
			
			$nodemat = false;
			if ( is_array($places[$rec["manifid"]]) )
			foreach ( $places[$rec["manifid"]] as $key => $resa )
			if ( $resa["nb"] != 0 )
			{
				$resumeClassTmp = $resumeClass;
				if ( $stage == 3 )
				if ( $resa["printed"] )
				{
					if ( $resa["canceled"] )
						$resumeClassTmp .= " warn";
					else
					{
						$resumeClassTmp .= " done";
						if ( !$data["dematerialized"] ) $nodemat = true;
					}
				}
				
				// sommes nous en précense d'une place numérotée ?
				$placement = count(split("plnum-",$resa["other"])) > 1;
				
				echo '<span class="'.$resumeClassTmp.'" id="billets'.$rec["manifid"].'.'.intval($key).'" ';
				echo $stage == 2 && !$placement ? 'onclick="javascript: this.parentNode.removeChild(this)"' : "";
				echo '>';
				
				echo '<span class="billet">';
				echo htmlsecure($resa["nb"].' '.$resa["tarif"].' '.$resa["reduc"]);
				// BETA
				if ( !$placement )
				echo '<input type="hidden" name="billet['.intval($rec["manifid"]).'][]" value="'.htmlsecure($resa["full"]).'" />';
				echo '</span>';
				
				// Affichage des résultats liés aux impressions
				if ( $print || $stage > 6 )
					echo '<span class="desc nonval">Billet(s) annulé(s) après impression</span><span class="desc imp">Billet(s) imprimé(s) et validé(s)</span><span class="desc nonimp">Billet(s) encore non imprimé(s)</span><span class="desc cancel">Billet(s) annulé(s) avant impression</span>';
				else if ( $stage == 2 && !$placement )	echo '<span class="desc">cliquer sur la réservation pour la supprimer</span>';
				else echo '<span class="desc">Billet(s) pré-réservé(s)</span>';
				
				echo '</span>';
				
				// Affichage des options d'impression
				if ( $print && !$data["dematerialized"] )
				{
					echo '<span class="valid"><span><span class="visu"></span>';
					if ( $config["ticket"]["placement"] )
					{
						// on ajoute les numéros prédéfinis lors de l'annulation
						$buf = $plnum[intval($rec["manifid"])][$resa["tarif"].$resa["reduc"]];
						if ( is_array($buf)
						  && count($buf) > 0
						  && strpos($_SERVER["HTTP_REFERER"],$config["website"]["base"]."evt/bill/annul.php") !== false )
							$resa["other"] = $buf;
						else	$resa["other"] = array();
					  
					  /*
					  if ( strpos($_SERVER["HTTP_REFERER"],$config["website"]["base"]."evt/bill/annul.php") !== false )
					    plnum($rec,$resa,strpos($_SERVER["HTTP_REFERER"],$config["website"]["base"]."evt/bill/annul.php") !== false);
					  */
					}
					echo '<input	type="button" name="print"';
					echo '		onclick="javascript: '."printBill(this,".intval($rec["manifid"]).",'".$resa["full"]."',".intval($key).",".$data["numtransac"].');"';
					echo '		value="Imprimer"/>';
					echo '</span></span>';
				}
			}
			
			echo '<span class="total"> = '.floatval($prices[$rec["manifid"]]).' €</span>';
			echo '<input type="hidden" name="total['.$rec["manifid"].']" value="'.floatval($prices[$rec["manifid"]]).'" />';
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
		
		// Total financier (fonction)
		echo '<p class="total">';
			echo '<span></span><span></span><span></span><span></span><span></span><span></span>';
			if ( !$print ) echo '<span></span>';
			echo '<span class="nom">Total</span>';
			echo '<span class="total"> = '.floatval($prices[0]).' €</span>';
		echo '</p>';
	?>
	</div>
	</div>
	
	<div class="ticketoptions">
	<?php if ( $stage == 3 && $config["ticket"]["dematerialized"] && !$nodemat ) { ?>
	<p class="dematerialized" onclick="javascript: printDematerialized(<?php echo htmlsecure($data["numtransac"]) ?>);"><a id="demat" href="evt/bill/dematerialized.php?t=<?php echo htmlsecure($data["numtransac"]) ?>" target="demat">Billet virtuel</a></p>
	<?php } if ( $stage == 3 && $config["ticket"]["enable_group"] ) { ?>
	<p class="billets-groupes"><input type="checkbox" name="group" value="yes" /> billet groupé</p>
	<?php } ?>
	</div>
	
	<?php if ( $stage == 4 ) { ?>
	<div class="reglement"><?php includePage("grp-reglement"); ?></div>
	<?php } if ( $stage < 3 ) { ?>
	<div class="valid">
		<p class="submit"><input type="submit" name="add" value="Valider" /></p>
		<?php if ( $prices != array(0 => 0) ) { ?>
			<p class="print"><input type="submit" name="print" value="Extraire un BdC" /></p>
		<?php } ?>
		<?php
			$request = new bdRequest($bd,"SELECT count(*) AS nb FROM bdc WHERE transaction = ".$data["numtransac"]);
			if ( intval($request->getRecord("nb")) > 0 )
			echo '<p class="print"><input type="submit" name="delbdc" value="Annuler le BdC" /></p>';
		?>
		<p class="print" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
			<input onclick="javascript: ttt_spanCheckBox(this);" type="checkbox" name="msexcel" value="" />MSExcel
		</p>
	</div>
	<?php } ?>
	<p class="submit">
		<input	type="button" <?php if ( $stage > 2 ) echo 'disabled="disabled"' ?>
			onclick="javascript: location.replace('<?php echo htmlsecure($_SERVER["PHP_SELF"]) ?>');"
			class="back" value="<< Départ" name="back" />
		<?php if ( $stage == 2 ) { ?>
		<input type="submit" name="express" value="Flash" title="Retient le contexte actuel (spectateur, manifestation -> ventes sur place)" />
		<?php if ( is_array($_SESSION["evt"]["express"]) ) { ?><input type="submit" name="unexpress" value="Normal" title="Revient sur une billetterie normale" /><?php } ?>
		<?php } ?>
		<input type="submit" class="next" value="Suivant >>" name="<?php if ( $print ) echo "printed"; else echo $stage == 2 ? "filled" : "paid" ?>" />
	</p>
<?php
if ( $stage == 2 )
	includePage("manifs");
?>
</form>
<div class="reminder">
	<?php includePage("reminder"); ?>
</div>
</div>
<?php
	$bd->free();
 	includeLib("footer");
?>
