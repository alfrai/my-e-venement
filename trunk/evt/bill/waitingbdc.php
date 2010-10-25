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
	$class .= " bdc";
	$subtitle = "Personnes n'ayant pas encore renvoyé un bon de commande";
	$query  = " SELECT DISTINCT bdc.transaction, personne.*
		    FROM personne_properso AS personne, bdc, reservation_pre AS resa, transaction t, manifestation AS manif
		    WHERE bdc.transaction NOT IN (SELECT transaction FROM tickets2print WHERE printed = true AND canceled = false)
		      AND bdc.transaction = t.id
		      AND t.id = resa.transaction
		      AND resa.manifid = manif.id
		      AND ( personne.fctorgid = t.fctorgid OR (personne.fctorgid IS NULL AND t.fctorgid IS NULL) )
		      AND personne.id = t.personneid
		      AND manif.date >= now() + '1 day'::interval
		      ".($_GET['spaces'] != 'all' ? 'AND t.spaceid '.($user->evtspace ? '= '.$user->evtspace : 'IS NULL') : '');
	
	includePage("late");
?>
