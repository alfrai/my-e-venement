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
	$query	= " SELECT manif.id AS manifid, evt.id, evt.nom, site.nom AS sitenom, site.ville, date
		    FROM evenement AS evt, manifestation AS manif, maniftosell AS mts, site
		    WHERE manif.evtid = evt.id
		      AND site.id = manif.siteid
		      AND mts.id = manif.id
		      AND manif.id = ".intval($_GET["manifid"]);
	$request = new bdRequest($bd,$query);
	
	if ( $request->countRecords() <= 0 )
	{
		$nav->addAlert("Manifestation demandée inexistante");
		$nav->redirect(".");
	}
	
	$data["manif"] = $request->getRecord();	// mise en forme logique des données
	$request->free();
	
	// tarifs pour cette manifestation
	$query	= " SELECT id, prix, prixspec
		    FROM tarif_manif
		    WHERE id IN ( SELECT id FROM tariftosell )
		      AND manifid = ".intval($data["manif"]["manifid"]);
	$tarifs = new bdRequest($bd,$query);
	$data["manif"]["pu"] = array();
	while ( $tarif = $tarifs->getRecordNext() )
		$data["manif"]["pu"][intval($tarif["id"])]
			= floatval($tarif["prixspec"] ? $tarif["prixspec"] : $tarif["prix"]);
	$tarifs->free();
	
	// récup des tarifs intéressants
	$query	= " SELECT * FROM tarif, tariftosell AS tss WHERE tss.id = tarif.id ORDER BY priority DESC, description";
	$request = new bdRequest($bd,$query);
	
	$data["tarifs"] = array();
	while ( $rec = $request->getRecordNext() )
		$data["tarifs"][] = $rec;
	
	$request->free();
	$bd->free();
?>
