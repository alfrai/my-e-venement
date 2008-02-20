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
	if (   $_SERVER["SCRIPT_NAME"] == $config["website"]["root"]."web/inc/list-evt.page.php"
	    || !isset($config) )
		exit(1);
	
	includeClass("bdRequest");
	require("conf.inc.php");
	
	// ajouter une contrainte sur les types de spectacles vendus en ligne
	$query	= " SELECT DISTINCT evt.id, evt.nom,
			   (SELECT nom FROM organisme WHERE id = evt.organisme1) AS orgnom1, 
			   (SELECT nom FROM organisme WHERE id = evt.organisme2) AS orgnom2, 
			   (SELECT nom FROM organisme WHERE id = evt.organisme3) AS orgnom3,
			   (SELECT id FROM organisme WHERE id = evt.organisme1) AS orgid1, 
			   (SELECT id FROM organisme WHERE id = evt.organisme2) AS orgid2, 
			   (SELECT id FROM organisme WHERE id = evt.organisme3) AS orgid3,
			   MAX(date) AS maxdate, MIN(date) AS mindate
		    FROM evenement AS evt, manifestation AS manif
		    WHERE manif.date > NOW()
		      AND evt.id = manif.evtid
		    GROUP BY evt.id, evt.nom, evt.organisme1, evt.organisme2, evt.organisme3
   		    ORDER BY mindate, evt.nom";
	$request = new bdRequest($bd,$query);
?>
<ul><?php
	while ( $rec = $request->getRecordNext() )
	{
		echo '<li>';
		echo '<a class="manif" href="web/manifs.php?evtid='.intval($rec["id"]).'">'.htmlsecure($rec["nom"]).'</a> ';
		
		// préparation des compagnies
		$cies = array();
		for ( $i = 1 ; $i < 4 ; $i++ )
		if ( intval($rec["orgid".$i]) > 0 )
			$cies[] = array("id" => $rec["orgid".$i], "nom" => $rec["orgnom".$i]);
		
		// affichage s'il y en a
		if ( count($cies) > 0 )
		{
			echo '<span class="cie">(';
			$i = 0;
			foreach ( $cies as $value )
			{
				if ( $i > 0 ) echo ", ";
				echo '<a href="web/compagnie.php?id='.intval($value["id"]).'">'.htmlsecure($value["nom"]).'</a>';
				$i++;
			}
			echo ')</span>';
		}
		
		// affichage des dates
		echo ' <span class="dates">('.htmlsecure('du '.date($config["format"]["date"],strtotime($rec["mindate"])).' au '.date($config["format"]["date"],strtotime($rec["maxdate"]))).')</span> ';

		echo '</li>';
	}
?></ul>
<?php
	$request->free();
	$bd->free();
?>
