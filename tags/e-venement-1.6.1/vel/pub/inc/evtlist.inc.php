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
*    Copyright (c) 2006-2008 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	$query	= " SELECT evt.id, evt.nom, evt.typedesc, evt.categorie, evt.catdesc, evt.metaevt,
		           evt.ages, evt.description, min(date) AS nextdate,
		           (SELECT nom FROM organisme WHERE id = organisme1) AS org1,
		           (SELECT nom FROM organisme WHERE id = organisme2) AS org2,
		           (SELECT nom FROM organisme WHERE id = organisme3) AS org3
		    FROM evenement_categorie AS evt, manifestation AS manif, maniftosell AS mts
		    WHERE manif.evtid = evt.id
		      AND manif.id = mts.id
		      AND manif.date >= NOW()
		      ".($evtid ? "AND evt.id = ".$evtid : "")."
		    GROUP BY evt.id, evt.nom, evt.typedesc, evt.categorie, evt.catdesc, evt.metaevt,
		             evt.ages, evt.description, org1, org2, org3
		    ORDER BY nextdate, nom";
	if ( $limit )
	$query .= " LIMIT ".intval($param["nbevts"]);
	$nextevts = new arrayBdRequest($bd,$query);
	
	$data = array();	// mise en forme logique des données
	while ( $evt = $nextevts->getRecordNext() )
	{
		$data[] = $evt;
		
		$query = " SELECT manif.id, date, site.nom AS sitenom, site.ville, manif.jauge, mts.jauge AS jaugevel
			   FROM manifestation AS manif, site, maniftosell AS mts
			   WHERE mts.id = manif.id
			     AND site.id = manif.siteid
			     AND date >= NOW()
			     AND evtid = ".intval($evt["id"])."
			   ORDER BY date, ville";
		$manif = new bdRequest($bd,$query);
		
		while ( $rec = $manif->getRecordNext() )
		{
			$rec["urls"]["addtocart"] = "addtocart.php?manifid=".intval($rec["id"]);
			$data[count($data)-1]["manifs"][] = $rec;
		}
		
		$manif->free();
	}
	
	$nextevts->free();
	$bd->free();
?>
