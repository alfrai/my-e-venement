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
	includeLib("actions");
	includeJS("ajax");
	includeJS("annu");
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	includeLib("headers");
	
	// le classique
	if ( $_GET["s"] )
		$name_start = strtoupper(trim("".$_GET["s"]));
	else	$name_start = $_GET["v"] ? "" : "A";
	$ville_start = $_GET["v"] ? strtoupper(trim("".$_GET["v"])) : "";
	$query = " SELECT *
		   FROM organisme_categorie
		   WHERE nom ILIKE '".$name_start."' || '%'
		     AND ville ILIKE '".$ville_start."' || '%'
		   ORDER BY nom,ville";
	
	// les doublons
	if ( isset($_GET["dbl"]) )
	$query = " SELECT organisme.id, organisme.catdesc, organisme.nom, organisme.ville
		   FROM (SELECT count(*) AS nb, lower(nom) AS nom, lower(ville) AS ville FROM organisme GROUP BY lower(nom), lower(ville)) AS tmp, organisme_categorie AS organisme
		   WHERE nb > 1
		     AND tmp.nom = lower(organisme.nom)
		     AND tmp.ville = lower(organisme.ville)
		   ORDER BY nom, ville, id";
		   
	$request = new bdRequest($bd,$query);
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require('actions.php'); ?>
<div class="body">
<h2>
	Liste des organismes dont <?php if ( $name_start ) { ?>le nom commence par "<?php echo htmlsecure($name_start); ?>"
	<?php } if ( $name_start && $ville_start ) echo "et"; if ( $ville_start ) { ?>
	la ville commence par "<?php echo htmlsecure($ville_start); ?>"
	<?php } ?>
</h2>
<p class="search top">
	<form name="formu" action="<?php echo htmlsecure($_SERVER["PHP_SELF"]) ?>" method="get">
		Recherche express sur le nom de l'organisme&nbsp;:
		<br />
		<input type="text" name="s" onkeyup="javascript: annu_org(this,'<?php echo htmlsecure($_GET["s"]) ?>');" id="focus" value="" />
		<br />
		Recherche express sur le nom de la ville (appuyer sur &lt;ENTER&gt; pour valider)&nbsp;:
		<br />
		<input type="text" name="v" value="" />
		<input type="submit" name="n" value="" class="disable" />
	</form>
</p>
<p class="letters top">
<?php
	$alphabet = "abcdefghijklmnopqrstuvwxyz";
	for ( $i = 0 ; $cur = strtoupper($alphabet{$i}) ; $i++ )
		echo '<a href="org/?s='.$cur.'">'.$cur.'</a> ';
	echo ' - <a href="org/?dbl">doublons</a>';
?>
</p>
<ul class="organismes" id="organismes">
	<?php
		while ($rec =  $request->getRecordNext() )
		{
			echo '<li><a href="org/fiche.php?view&id='.$rec["id"].'">';
			echo htmlsecure($rec["nom"]).'</a>';
			echo htmlsecure(' ('.$rec["catdesc"].($rec["catdesc"] && $rec["ville"] ? ' - ' : '').$rec["ville"].')');
			echo '<span class="hidden id">'.intval($rec['id']).'</span>';
			echo '</li>';
		}
	?>
</ul>
<p class="letters">
<?php
	for ( $i = 0 ; $cur = strtoupper($alphabet{$i}) ; $i++ )
		echo '<a href="org/?s='.$cur.'">'.$cur.'</a> ';
?>
</p>
</div>
<?php
	$request->free();
	$bd->free();
	includeLib("footer");
?>
