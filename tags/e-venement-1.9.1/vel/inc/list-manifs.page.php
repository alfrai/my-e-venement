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
*    Copyright (c) 2006-2007 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	@include_once("../../config.php");
	@include_once("../config.php");
	global $config;
	
	// on vérifie si on n'est pas sur un accès direct
	if (   $_SERVER["SCRIPT_NAME"] == $config["website"]["root"]."web/inc/list-manifs.page.php"
	    || !isset($config) || intval($_GET["evtid"]) <= 0 )
		exit(1);
	
	includeClass("bdRequest");
	require("conf.inc.php");
	
	// ajouter une contrainte sur les types de spectacles vendus en ligne
	$query	= " SELECT evt.id, evt.nom, manif.id, manif.date, site.nom, site.ville, manif.id AS manifid,
			   (SELECT nom FROM organisme WHERE id = evt.organisme1) AS orgnom1, 
			   (SELECT nom FROM organisme WHERE id = evt.organisme2) AS orgnom2, 
			   (SELECT nom FROM organisme WHERE id = evt.organisme3) AS orgnom3,
			   (SELECT id FROM organisme WHERE id = evt.organisme1) AS orgid1, 
			   (SELECT id FROM organisme WHERE id = evt.organisme2) AS orgid2, 
			   (SELECT id FROM organisme WHERE id = evt.organisme3) AS orgid3
		    FROM evenement AS evt, manifestation AS manif, site
		    WHERE manif.date > NOW()
		      AND evt.id = manif.evtid
		      AND manif.siteid = site.id
		      AND evt.id = ".intval($_GET["evtid"])."
   		    ORDER BY evt.nom, manif.date";
	$request = new bdRequest($bd,$query);
	
?>
<form action="" method="post">
<ul><?php
	while ( $rec = $request->getRecordNext() )
	{
		$query	= " SELECT DISTINCT key, description, getprice(".intval($rec["manifid"]).",tarif.id) AS prix
			    FROM tarif
		            WHERE date IN ( SELECT max(date) FROM tarif AS tmp WHERE tmp.key = tarif.key )
		              AND NOT desact
		              AND NOT contingeant
		            ORDER BY description";
		$tarifs = new bdRequest($bd,$query);
	
		echo '<li>';
		
		echo date($config["format"]["date"].' '.$config["format"]["maniftime"],strtotime($rec["date"]))." ";
		echo '<input type="text" name="cmd['.$rec["manifid"].'][0][nb]" maxlength="2" size="3" value="" />';
		echo '<select name="cmd['.$rec["manifid"].'][0][tarif]">';
		echo '<option value="">-- les tarifs --</option>';
		while ( $tar = $tarifs->getRecordNext() )
			echo '<option value="'.htmlsecure($tar["key"]).'">'.htmlsecure($tar["description"]).' ('.round(floatval($tar["prix"]),2).'€)</option>';
		echo '</select>';
		
		// affichage des places déjà demandées
		$i = 1;
		if ( is_array($_POST["cmd"][$rec["manifid"]]) )
		foreach ( $_POST["cmd"][$rec["manifid"]] AS $value )
		{
			echo '<input type="hidden" name="cmd['.$rec["manifid"].']['.$i.'][nb]" value="'.intval($value["nb"]).'" />';
			echo '<input type="hidden" name="cmd['.$rec["manifid"].']['.$i++.'][tarif]" value="'.htmlsecure($value["tarif"]).'" />';
			echo '<span class="ticket">'.intval($value["nb"]).htmlsecure($value["tarif"]).'</span>';
		}
		
		echo '</li>';
		
		$tarifs->free();
	}
?></ul>
<p>
	<input type="submit" value="Ajouter" name="maj" />
	<input type="submit" value="Valider" name="submit" />
</p>
</form>
<?php
	$tarifs->free();
	$request->free();
	$bd->free();
?>
