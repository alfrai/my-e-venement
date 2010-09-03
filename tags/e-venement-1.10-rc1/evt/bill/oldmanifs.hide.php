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
	includeLib("bill");
	includeLib("jauge");
	includeClass("bdRequest/array");
	$jauge = true;
	
	// Les spectacles
	$query	= "SELECT evt.id AS id, manif.id AS manifid, evt.nom, site.nom AS sitenom, site.ville,
		          manif.date, evt.categorie, evt.catdesc, colors.libelle AS colorname
		   FROM evenement_categorie AS evt, manifestation AS manif, site, colors
		   WHERE manif.date <= NOW() - '1 hour'::interval
		     AND jauge > 0
		     AND ( colors.id = manif.colorid OR manif.colorid IS NULL AND colors.id IS NULL )
		     AND evt.id = manif.evtid
		     AND site.id = manif.siteid
		   ORDER BY catdesc, nom, date";
	$evt = new arrayBdRequest($bd,$query);
	
	$lastcat = -1;
	while ( $rec = $evt->getRecordNext() )
	{
		echo '<p class="content" onmouseover="javascript: bill_jauge('.intval($rec["manifid"]).');"
					 onclick="javascript: '."ttt_spanCheckBox(this.getElementsByTagName('input').item(0))".';">';
		echo '<span class="sel">';
		echo '<input type="checkbox" name="manif[]" value="'.$rec["manifid"].'" onclick="javascript: '."ttt_spanCheckBox(this)".';" ';
		echo '/>';
		echo '</span> ';
		printManif($rec);
		echo '</p> ';
		if ( $jauge )
		{
			echo '<p class="content jauge" onclick="javascript: '."ttt_spanCheckBox(this.parentNode.getElementsByTagName('input').item(0))".';">';
			echo '<span id="manif_'.intval($rec["manifid"]).'">';
			//printJauge(intval($rec["jauge"]),intval($rec["preresas"]),intval($rec["resas"]),450,intval($rec["commandes"]),550);
			echo '</span></p>';
		}
	}
	
	$evt->free();
	$bd->free();
?>
