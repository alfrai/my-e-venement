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
	includeJS("annu");
	includeJS("ajax");
	
	$urls["base"] = "pro";
	$urls["fiche"] = "pro.php";
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	$action = $actions["edit"];
	
	// valeurs par défaut (la clé du tableau doit etre la même que la clé du tableau passé en POST)
	$default["nom"] = "-DUPORT-";
	
	includeLib("headers");
	
	$name_start = "";
	if ( !isset($_GET["p"]) && !$_GET["s"] )
		$name_start = "A";
	else if ( $_GET["s"] )
		$name_start = trim("".htmlsecure($_GET["s"]));
	
	$query	= " SELECT *
		    FROM personne_properso
		    WHERE LOWER(nom) LIKE LOWER('".$name_start."') || '%'
		      AND fctorgid IS NOT NULL";
	if ( isset($_GET["p"]) )
	$query .= "   AND fctorgid IN (SELECT fctorgid FROM roadmap)";
	$query .= " ORDER BY nom,prenom,orgnom";
	$personnes = new bdRequest($bd,$query);
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
<h2>Liste des professionnels dont le nom commence par "<span id="start"><?php echo strtoupper(htmlsecure($name_start)); ?></span>"</h2>
<p class="search top">
	<form name="formu" action="<?php echo $_SERVER["PHP_SELF"]?>" method="GET">
		Recherche sur le nom de famille&nbsp;:<br />
		<input type="text" name="s" id="focus" value="" />
		<input type="checkbox" name="p" value="only" onchange="javascript: this.form.submit();" /><span class="desc">N'afficher que les pros présents</span>
	</form>
</p>
<p class="letters top">
<?php
	$alphabet = "abcdefghijklmnopqrstuvwxyz";
	for ( $i = 0 ; $cur = strtoupper($alphabet{$i}) ; $i++ )
		echo '<a href="'.$urls["base"].'/?s='.$cur.'">'.$cur.'</a> ';
?>
</p>
<ul class="contacts" id="personnes">
	<?php
		while ($rec =  $personnes->getRecordNext() )
		{
			$class = $rec["npai"] == 't' ? "npai" : "";
			echo '<li class="'.$class.'">';
			echo '<a href="ann/fiche.php?id='.intval($rec["id"]).'" class="annu" onmouseover="javascript: annu_persmicrofiche('.intval($rec["id"]).');"><span>fiche contact</span></a><span class="desc">Accéder à la fiche du contact</span>';
			echo '<a href="'.$urls["base"].'/'.$urls["fiche"].'?fctorgid='.$rec["fctorgid"].'&view" onmouseover="javascript: annu_persmicrofiche('.intval($rec["id"]).');">';
			echo htmlsecure($rec["nom"].' '.$rec["prenom"]);
			echo '</a> (<a href="org/fiche.php?id='.intval($rec["orgid"]).'&view">'.htmlsecure($rec["orgnom"]).'</a> - ';
			echo htmlsecure($rec["fctdesc"] ? $rec["fctdesc"] : $rec["fcttype"]).')';
			echo '</li>';
		}
	?>
</ul>
<p id="ficheindiv"></p>
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
