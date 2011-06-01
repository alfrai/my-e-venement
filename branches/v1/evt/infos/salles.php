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
	$salle = true;
	$class = salle;
	
	includeLib("headers");
	
	
	$name_start = $_GET["s"] ? trim("".htmlsecure($_GET["s"])) : '';
	$query = " SELECT id, nom, ville, SUBSTR(cp,1,2) AS cp
		   FROM site
		   WHERE LOWER(ville) LIKE LOWER('".$name_start."') || '%'
		   ORDER BY nom, cp, ville";
	$salles = new bdRequest($bd,$query);
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
	<h2>Les salles</h2>
	<h3>Liste des salles dont le nom de la ville commence par "<span id="start"><?php echo strtoupper(htmlsecure($name_start)); ?></span>"</h3>
	<p class="search top">
		<form name="formu" action="<?php echo $_SERVER["PHP_SELF"] ?>" method="get">
			Recherche express par ville&nbsp;:<br />
			<input type="text" name="s" onkeyup="javascript: annu_salles(this,'<?php echo htmlsecure($_GET["s"]) ?>');" id="focus" />
		</form>
	</p>
	<p class="letters top">
	<?php
		$alphabet = "abcdefghijklmnopqrstuvwxyz";
		for ( $i = 0 ; $cur = strtoupper($alphabet{$i}) ; $i++ )
			echo '<a href="evt/infos/salles.php?s='.$cur.'">'.$cur.'</a> ';
	?>
</p>
<ul class="<?php echo htmlsecure($class) ?>" id="salle">
	<?php
		while ($rec =  $salles->getRecordNext() )
		{
			$class = $rec["npai"] == 't' ? "npai" : "";
			echo '<li class="'.$class.'">';
			echo '<a href="evt/infos/salle.php?id='.intval($rec["id"]).'&view">'.htmlsecure($rec["nom"]).'</a> ';
			echo '<span>'.htmlsecure('('.($rec["cp"] ? $rec["cp"].', ' : '').$rec["ville"].') ').'</span>';
			echo '</li>';
		}
	?>
</ul>
<p class="letters bottom">
<?php
	for ( $i = 0 ; $cur = strtoupper($alphabet{$i}) ; $i++ )
		echo '<a href="evt/infos/salles.php?s='.$cur.'">'.$cur.'</a> ';
?>
</p>
</div>
<?php
	includeLib("footer");
?>
