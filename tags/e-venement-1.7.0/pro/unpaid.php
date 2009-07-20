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
	
	$query	= " SELECT pers.id, pers.nom, pers.prenom, pers.orgnom, pers.orgid, pers.fctorgid, count(roadmap.paid) AS unpaid,
		           SUM(billeterie.getprice(roadmap.manifid,(SELECT value FROM params WHERE name = 'tarifpros' LIMIT 1))) AS prix
		    FROM roadmap, personne_properso AS pers
		    WHERE pers.fctorgid IS NOT NULL
		      AND pers.fctorgid = roadmap.fctorgid
		      AND NOT paid
		      AND NOT is_auto_paid(manifid)
		    GROUP BY pers.id, pers.nom, pers.prenom, pers.orgnom, pers.orgid, pers.fctorgid
		    ORDER BY nom,prenom,orgnom";
	$personnes = new bdRequest($bd,$query);
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
<h2>Liste des professionnels ayant des manifestations notées comme "impayées"</h2>
<ul class="contacts" id="personnes">
	<?php
		while ($rec =  $personnes->getRecordNext() )
		{
			$class = $rec["npai"] == 't' ? "npai" : "";
			echo '<li class="'.$class.'">';
			echo '<a href="ann/fiche.php?id='.intval($rec["id"]).'" class="annu"><span>fiche contact</span></a><span class="desc">Accéder à la fiche du contact</span>';
			echo '<a href="'.$urls["base"].'/'.$urls["fiche"].'?fctorgid='.$rec["fctorgid"].'&view">';
			echo htmlsecure($rec["nom"].' '.$rec["prenom"]);
			echo '</a> (<a href="org/fiche.php?id='.intval($rec["orgid"]).'&view">'.htmlsecure($rec["orgnom"]).'</a> - ';
			echo htmlsecure($rec["fctdesc"] ? $rec["fctdesc"] : $rec["fcttype"]).')';
			echo "&nbsp;: ".intval($rec["unpaid"])." impayés pour ".round(floatval($rec["prix"]),2)."€ au total";
			echo '</li>';
		}
	?>
</ul>
</div>
<?php
	$personnes->free();
	$bd->free();
	includeLib("footer");
?>
