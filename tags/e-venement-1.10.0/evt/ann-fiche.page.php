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
	$oldpath = $bd->getPath();
	$bd->setPath("billeterie,public");
	
	$infos = array();
	
	// billetterie actuelle
	$infos[] = array();
	$infos[count($infos)-1]["titre"] = "Billetterie actuelle";
	$query = " SELECT DISTINCT evt.id, evt.nom, transaction.id AS transaction
		   FROM reservation_pre AS preresa, reservation_cur AS resa,
		        manifestation AS manif, evenement AS evt, transaction
		   WHERE preresa.id = resa.resa_preid
		     AND NOT resa.canceled
		     AND preresa.manifid = manif.id
		     AND preresa.transaction = transaction.id
		     AND transaction.personneid = ".$id."
		     AND manif.evtid = evt.id
		     AND transaction NOT IN ( SELECT transaction FROM contingeant WHERE transaction = transaction.id )
		   ORDER BY nom, transaction";
	$request = new bdRequest($bd,$query);
	$evtid = false;
	
	$txt = "<ul>";
	while ( $rec = $request->getRecordNext() )
	{
		if ( $evtid != intval($rec["id"]) )
		{
			if ( $evtid ) $txt .= "</ul></li>";
			$evtid = intval($rec["id"]);
			$txt .= '<li><a href="evt/infos/fiche.php?id='.intval($rec["id"]).'">'.htmlsecure($rec["nom"])."</a><ul>";
		}
		$txt	.= '<li>#<a href="'.($_SESSION['ticket']['old-bill'] ? 'evt/bill/billing.php?t=' : 'evt/bill/new-bill.php?t=').htmlsecure($rec["transaction"]).'">'.htmlsecure($rec["transaction"]).'</a></li>';
	}
	if ( $evtid ) $txt .= "</ul></li>";
	$txt .= "</ul>";
	$txt .= '<p><a href="evt/bill/credit.php?id='.$id.'">Suivi de compte...</a></p>';
	
	$infos[count($infos)-1]["contenu"] = $txt;
	$request->free();
	
	// billetterie archivée
	$query	= " SELECT DISTINCT evenement, EXTRACT(YEAR FROM date) AS date
		    FROM personne_evtbackup
		    WHERE personneid = ".$id."
		    ORDER BY date";
	$request = new bdRequest($bd,$query);
	$backup = false;
	
	$infos[] = array();
	$infos[count($infos)-1]["titre"]	= "Billetterie passée";
	$txt = "<ul>";
	while ( $rec = $request->getRecordNext() )
	{
		if ( $backup != intval($rec["date"]) )
		{
			if ( $backup ) $txt .= "</ul></li>";
			$txt .= "<li>";
			$backup = intval($rec["date"]);
			$txt .= $backup."<ul>";
		}
		$txt	.= "<li>".htmlsecure($rec["evenement"])."</li>";
	}
	if ( $backup ) $txt	.= "</ul></li>";
	$txt	.= "</ul>";
	
	$infos[count($infos)-1]["contenu"]	= $txt;
	$request->free();
	
	$bd->setPath($oldpath);
?>
