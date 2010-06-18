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
	if ( is_array($_SESSION["vel"]["cart"]) && count($_SESSION["vel"]["cart"]) > 0 )
	{
		$data = array();
		
		// gestion des tarifs
		$data["tarifs"] = array();
		$query	= " SELECT tarif.*, tss.priority
			    FROM tarif, tariftosell AS tss
			    WHERE tss.id = tarif.id
			    ORDER BY tss.priority, tarif.key";
		$request = new bdRequest($bd,$query);
		while ( $rec = $request->getRecordNext() )
			$data["tarifs"][intval($rec["id"])] = $rec;
		$request->free();
		
		// gestion des manifestations
		$manifs = array();
		foreach ( $_SESSION["vel"]["cart"] as $key => $value )
			$manifs[] = intval($key);
		
		$query	= " SELECT evt.nom, evt.id, manif.id AS manifid, manif.date,
			           site.nom AS sitenom, site.ville,
			           mts.jauge - mts.selled AS free
			    FROM evenement AS evt, manifestation AS manif, maniftosell AS mts, site
			    WHERE evt.id = manif.evtid
			      AND site.id = manif.siteid
			      AND mts.id = manif.id
			      AND manif.id IN (".implode(",",$manifs).")
			    ORDER BY evt.nom, date";
		$request = new bdRequest($bd,$query);
		
		$data["manifs"] = array();
		
		while ( $rec = $request->getRecordNext() )
		{
			$data["manifs"][] = $rec;
			$data["manifs"][count($data["manifs"])-1]["qty"] 
				= $_SESSION["vel"]["cart"][intval($rec["manifid"])];
			
			// tarifs pour cette manifestation
			$query	= " SELECT id, prix, prixspec
				    FROM tarif_manif
				    WHERE id IN ( SELECT id FROM tariftosell )
				      AND manifid = ".intval($rec["manifid"]);
			$tarifs = new bdRequest($bd,$query);
			while ( $tarif = $tarifs->getRecordNext() )
				$data["manifs"][count($data["manifs"])-1]["pu"][intval($tarif["id"])]
					= floatval($tarif["prixspec"] ? $tarif["prixspec"] : $tarif["prix"]);
			$tarifs->free();
		}
		
		$request->free();
	}
	
	if ( isset($_GET["cmd"]) ) $data["cmd"] = true;
?>
