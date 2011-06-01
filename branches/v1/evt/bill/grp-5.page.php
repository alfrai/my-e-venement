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
	global $bd,$user,$nav,$class,$data,$title,$stage,$subtitle;
	global $redirectnewurl; // pour la billetterie express
	
	$class .= ' finish';
	
	if ( is_array($_SESSION["evt"]["express"]) )
		$redirectnewurl = $_SERVER["PHP_SELF"];
	
	// on bloque la transaction
	$bd->updateRecordsSimple('transaction',array('id' => $data['numtransac']),array('blocked' => 't'));
	
	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
<?php
	if ( $subtitle ) echo htmlsecure($subtitle);
	includePage("grp-stages");
?>
<p class="numtransac"><span>Numéro d'opération:</span> <span>#<?php echo htmlsecure($data["numtransac"]) ?></span></p>
<p class="recorded"><?php
	echo 'Enregitrement des réservations effectué.';
?></p>
<?php
		// affichage des doléances
		$properso['id']	= intval(substr($data["client"],5));
		$properso['pro']= substr($data["client"],0,4) == "prof";
		
		$query = " SELECT *
			   FROM personne_properso
			   WHERE ".( $properso['pro']
			          ? "fctorgid = ".$properso['id']
			          : "id = ".$properso['id']." AND fctorgid IS NULL");
		$request = new bdRequest($bd,$query);
		$rec = $request->getRecord();
		
		echo '<p class="merci">';
		echo 'Merci ';
		echo htmlsecure($rec["titre"].' '.$rec["prenom"].' '.$rec["nom"]);
		echo '<a href="ann/fiche.php?id='.intval($rec["id"]).'&view"> </a>';
		echo ', et bon(s) spectacle(s)&nbsp;!';
		echo '</p>';
		
		$request->free();
?>
<p class="back">&lt;&lt; <a href="<?php echo htmlsecure($_SERVER["PHP_SELF"]) ?>">Retour</a> à l'accueil billetterie</p>
</div>
<?php
 	includeLib("footer");
?>
