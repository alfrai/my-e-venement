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
	includeJS("ajax");
	includeJS("annu");
	$evt = true;
	$class = evt;
	
	includeLib("headers");
	
	if ( $_GET["c"] )
	{
		// recherche par nom d'organisme
		$query = " SELECT evt.id, evt.catdesc, evt.nom
			   FROM evenement_categorie AS evt, organisme
			   WHERE (organisme.id = evt.organisme1
			       OR organisme.id = evt.organisme2
			       OR organisme.id = evt.organisme3)
			     AND organisme.nom ILIKE '%".pg_escape_string($_GET["c"])."%'";
	}
	else
	{
		// recherche par nom d'évènement
		$name_start = $_GET["s"] ? trim("".pg_escape_string($_GET["s"])) : "A";
		$query = " SELECT id, catdesc, nom
			   FROM evenement_categorie
			   WHERE LOWER(nom) LIKE LOWER('".$name_start."') || '%'
			   ORDER BY catdesc,nom";
	}
	$events = new bdRequest($bd,$query);
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
	<h2>Les évènements</h2>
	<h3>Liste des évènements commençant par "<span id="start"><?php echo strtoupper(htmlsecure($name_start)); ?></span>"</h3>
	<p class="search top">
		<form name="formu" action="<?php echo $_SERVER["PHP_SELF"] ?>" method="get">
			Recherche express par nom de spectacle&nbsp;: 
			<input type="text" name="s" onkeyup="javascript: annu_evt(this,'<?php echo htmlsecure($_GET["s"]) ?>');" id="focus" value="" />
		</form>
	</p>
	<p class="search top">
		<form name="formu" action="<?php echo $_SERVER["PHP_SELF"] ?>" method="get">
			Recherche par nom de compagnie&nbsp;: 
			<input type="text" name="c" value="<?php echo $_GET["c"] ? htmlsecure($_GET["c"]) : '' ?>" />
		</form>
	</p>
	<p class="nummanif top">
		<form name="formu" action="evt/infos/manif.php" method="post">
			Recherche par numéro de manifestation&nbsp;: 
			<input type="text" name="id" value="" />
		</form>
	</p>
	<p class="letters top">
	<?php
		$alphabet = "abcdefghijklmnopqrstuvwxyz";
		for ( $i = 0 ; $cur = strtoupper($alphabet{$i}) ; $i++ )
			echo '<a href="evt/infos/?s='.$cur.'">'.$cur.'</a> ';
	?>
</p>
<ul class="events" id="events">
	<?php
		while ($rec =  $events->getRecordNext() )
		{
			$class = $rec["npai"] == 't' ? "npai" : "";
			echo '<li class="'.$class.'"><span class="puce"> </span>';
			echo '<a href="evt/infos/fiche.php?id='.intval($rec["id"]).'&view">'.htmlsecure($rec["nom"]).'</a> ';
			echo '<span>'.htmlsecure('('.$rec["catdesc"].') ').'</span>';
			echo '</li>';
		}
	?>
</ul>
<p class="letters bottom">
<?php
	for ( $i = 0 ; $cur = strtoupper($alphabet{$i}) ; $i++ )
		echo '<a href="evt/infos/?s='.$cur.'">'.$cur.'</a> ';
?>
</p>
</div>
<?php
	$events->free();
	includeLib("footer");
?>
