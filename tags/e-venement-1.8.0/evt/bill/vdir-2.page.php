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
	global $bd,$user,$nav,$class,$data,$title,$stage,$oldtransac,$subtitle,$css;
	
	includeClass("reservations");
	includeLib("jauge");
	includeJS("ajax");
	includeJS("bill","evt");
	
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
		$clientid = intval($request->getRecord("id"));
		$perso  = $request->getRecord("titre")." ".$request->getRecord("nom")." ".$request->getRecord("prenom");
		$request->free();
	}
	
	$places = array();
	$resa = new reservations($bd,$user,$data["numtransac"]);
	
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
	includePage("vdir-stages");
?>
<p class="numtransac">
	<span><?php echo htmlsecure("Client: ".$perso) ?></span>
	;
	<span>Numéro d'opération:</span> <span>#<?php echo htmlsecure($data["numtransac"]) ?></span>
</p>
<form	name="formu" class="resa" method="post"
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
		$arr = array();
		foreach ( $places as $manif => $value )
		foreach ( $value as $bill )
		if ( $bill["printed"] == 't' )
			$arr[$manif][] = $bill["full"];
		if ( !isset($prices) )
			$prices = getPrices($data["numtransac"]);
		unset($arr);

		$query = " SELECT masstickets.*, tarif.key AS tarif INTO TEMP tmptickets
			   FROM masstickets, tarif
			   WHERE transaction = '".pg_escape_string($data["numtransac"])."'
			     AND tarif.id = tarifid
			     AND printed > 0";
		$request = new bdRequest($bd,$query);
		$request->free();
		
		//$query	= "SELECT * FROM info_resa ";
		$query  = " SELECT evt.id AS id, manif.id AS manifid, evt.nom, site.nom AS sitenom, site.ville,
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
			
			// Affichage des billets imprimés pour le dépôt de billeterie
			$query = " SELECT * FROM tmptickets WHERE manifid = ".intval($rec["manifid"]);
			$masstickets = new bdRequest($bd,$query);
			for ( $j = 0 ; $tick = $masstickets->getRecordNext() ; $j++ )
			if ( $tick["nb"] != 0 || $stage == 2 )
			{
				$resa = array();
				$resa[$name = "nb"]	= $tick[$name];
				$resa[$name = "tarif"]	= $tick[$name];
				$resa[$name = "reduc"]	= intval($tick[$name]) < 10 ? "0".intval($tick[$name]) : intval($tick[$name]);
				$resa[$name = "full"]	= $resa["nb"].$resa["tarif"].$resa["reduc"];
				$resa[$name = "manifid"]= $tick[$name];
					
				$resumeClassTmp = $resumeClass." mass";
				
				echo '<span class="'.$resumeClassTmp.'" id="billets'.$rec["manifid"].'.'.intval($key).'" ';
				echo '>';
				
				$inv = $_POST["invendu"][$resa["manifid"]];
				
				echo '<span class="billet invendu">';
				echo $stage < 3
					? '<input type="text" name="invendu['.intval($resa["manifid"]).']['.htmlsecure($resa["tarif"].$resa["reduc"]).']"
					  '.($i == 0 && $j == 0 ? 'id="focus"' : '').' value="'.$tick["nb"].'" onclick="'."javascript:if(this.value=='".$tick["nb"]."')this.value=''".'">'
					: intval($resa["nb"])." ";
				echo htmlsecure($resa["tarif"].' '.$resa["reduc"]);
				echo '<input type="hidden" name="billet['.intval($resa["manifid"]).'][]" value="'.htmlsecure($resa["full"]).'" />';
				echo '</span>';
				
				// Affichage des résultats liés aux impressions
				echo $stage < 3
					? '<span class="desc">Combien de billets invendus ont été retournés du dépôt ?</span>'
					: '<span class="desc">Billets invendus au dépôt</span>' ;
				
				echo '</span>';
			}
			$masstickets->free();
		
			// affichage des places contingeantées
			if ( is_array($places[$rec["manifid"]]) )
			foreach ( $places[$rec["manifid"]] as $key => $resa )
			if ( $resa["nb"] != 0 )
			{
				$resumeClassTmp = $resumeClass." contingeant";
				if ( $resa["printed"] == 't' ) $resumeClassTmp = $resumeClass." done";
				
				echo '<span class="'.$resumeClassTmp.'" id="billets'.$rec["manifid"].'.'.intval($key).'" ';
				echo '>';
				
				echo '<span class="billet">';
				echo htmlsecure($resa["nb"].' '.$resa["tarif"].' '.$resa["reduc"]);
				echo '</span>';
				
				// Affichage des résultats liés aux impressions
				echo '<span class="desc imp">place(s) vendue(s) en dépôt</span>';
				echo '<span class="desc">place(s) encore "contingeantée(s)"</span>';
				
				echo '</span>';
			}
			echo '<span class="total"> = '.floatval($prices[$rec["manifid"]]).' €</span>';
			echo '<input type="hidden" name="total['.$rec["manifid"].']" value="'.floatval($prices[$rec["manifid"]]).'" />';
			echo '</p>';
			if ( $jauge )
			{
				echo '<p class="content jauge" onclick="javascript: '."ttt_spanCheckBox(this.parentNode.getElementsByTagName('input').item(0))".';">';
				echo '<span id="manif_'.intval($rec["manifid"]).'">';
				//printJauge(intval($rec["jauge"]),intval($rec["preresas"]),intval($rec["resas"]),450,intval($rec["commandes"]),550);
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
	<?php if ( $stage == 3 ) { ?>
	<div class="reglement"><?php includePage("grp-reglement"); ?></div>
	<?php } ?>
	<p class="submit">
		<input	type="button"
			onclick="javascript: location.replace('<?php echo htmlsecure($_SERVER["PHP_SELF"]).( $stage > 2 ? '?t='.htmlsecure($data["numtransac"]) : '') ?>');"
			class="back" value="<< Départ" name="back" />
		<input type="submit" class="next" value="Suivant >>" name="<?php if ( $print ) echo "printed"; else echo $stage == 2 ? "filled" : "paid" ?>" />
	</p>
	<div class="reminder">
		<?php includePage("reminder"); ?>
	</div>
</form>
</div>
<?php
	$bd->free();
 	includeLib("footer");
?>
