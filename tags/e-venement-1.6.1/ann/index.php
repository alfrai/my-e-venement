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
	includeLib("actions");
	includeJS("ttt");
	includeJS("ajax");
	includeJS("annu");
	
	$urls["base"] = "ann";
	$urls["fiche"] = "fiche.php";
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	$action = $actions["edit"];
	
	// valeurs par défaut (la clé du tableau doit etre la même que la clé du tableau passé en POST)
	$default["nom"] = "-DUPORT-";
	
	includeLib("headers");
	
	// le classique
	$name_start = $_GET["s"] ? trim("".htmlsecure($_GET["s"])) : "A";
	$query	= " SELECT *
		    FROM personne
		    WHERE nom ILIKE '".$name_start."' || '%'
		    ORDER BY nom,prenom";
	
	// les doublons
	if ( isset($_GET["dbl"]) )
	$query = " SELECT personne.*
		   FROM (SELECT count(*) AS nb, lower(nom) AS nom, lower(prenom) AS prenom FROM personne GROUP BY lower(nom),lower(prenom)) AS tmp, personne
		   WHERE nb > 1
		     AND tmp.nom = lower(personne.nom)
		     AND tmp.prenom = lower(personne.prenom)
		   ORDER BY nom, prenom, id";
	
	$personnes = new bdRequest($bd,$query);
	
	// créer un groupe avec le contenu existant
	if ( isset($_GET["grp"]) )
	{
		// le nom du groupe
		$grpname = "annuaire_".(isset($_GET["dbl"]) ? "dbl_" : $_GET["s"]).date($config["format"]["sysdate"]);
		
		// clean de la table "groupe"
		if ( !$bd->delRecordsSimple("groupe",array("nom" => $grpname, "createur" => $user->getId())) )
			$user->addAlert("Impossible de supprimer le groupe précédent");
		
		// ajout du groupe
		$arr = array();
		$arr["createur"]	= $user->getId();
		$arr["nom"]		= $grpname;
		$arr["description"]	= "Groupe extrait de l'annuaire le ".date($config["format"]["date"].' à '.$config["format"]["ltltime"]);
		if ( $bd->addRecord("groupe",$arr) )
			$user->addAlert("Groupe ".$grpname." créé.");
		$grpid = $bd->getLastSerial("groupe","id");
		
		// remplissage de son contenu
		for ( $nb = 0 ; $rec = $personnes->getRecordNext() ; )
		{
			$arr = array();
			$arr["groupid"]		= $grpid;
			$arr["personneid"]	= intval($rec["id"]);
			$arr["included"]	= "t";
			if ( $bd->addRecord("groupe_personnes",$arr) )
				$nb++;
		}
		$user->addAlert($nb." enregistrements ajoutés.");
		
		// on remet la requete au début
		$personnes->firstRecord();
	}
	
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><?php printActions("ann"); ?></p>
<div class="body">
<h2>Liste des personnes dont le nom commence par "<span id="start"><?php echo strtoupper(htmlsecure($name_start)); ?></span>"</h2>
<p class="search top">
	<form name="formu" action="<?php echo htmlsecure($_SERVER["PHP_SELF"]) ?>" method="get">
		Recherche express sur le nom de famille (n'importe où dans le nom)&nbsp;:<br />
		<input type="text" name="s" onkeyup="javascript: annu_search(this,'<?php echo htmlsecure($_GET["s"]) ?>');" id="focus" value="" />
	</form>
</p>
<p class="letters top">
<?php
	$alphabet = "abcdefghijklmnopqrstuvwxyz";
	for ( $i = 0 ; $cur = strtoupper($alphabet{$i}) ; $i++ )
		echo '<a href="'.$urls["base"].'/?s='.$cur.'">'.$cur.'</a> ';
	echo ' - <a href="'.$urls["base"].'/?dbl">doublons</a>';
	echo ' - <a href="'.$urls["base"].'/?grp&s='.htmlsecure($_GET["s"]).(isset($_GET["dbl"]) ? "&dbl" : "").'">exporter</a>';
?>
</p>
<ul class="contacts" id="personnes">
	<?php
		while ($rec =  $personnes->getRecordNext() )
		{
			$class = $rec["npai"] == 't' ? "npai" : "";
			echo '<li class="'.$class.'"><a href="'.$urls["base"].'/'.$urls["fiche"].'?id='.$rec["id"].'&view">';
			echo htmlsecure($rec["nom"].' '.$rec["prenom"]);
			echo '</a></li>';
		}
	?>
</ul>
<p class="letters bottom">
<?php
	for ( $i = 0 ; $cur = strtoupper($alphabet{$i}) ; $i++ )
		echo '<a href="'.$urls["base"].'/?s='.$cur.'">'.$cur.'</a> ';
?>
</p>
</div>
<?php
	$personnes->free();
	$bd->free();
	includeLib("footer");
?>
