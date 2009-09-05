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
	$class .= " queries";
	$subtitle = "Personnes ayant des demandes en attente de réponse";
	
	/*
	$query  = " SELECT DISTINCT transaction, personne.*
		    FROM reservation_pre AS resa, personne_properso AS personne, transaction, manifestation AS manif
		    WHERE resa.id IN (SELECT id FROM tickets2print WHERE canceled = false AND printed = false)
		      AND resa.transaction = transaction.id
		      AND ( personne.id = transaction.personneid OR personne.id IS NULL AND transaction.personneid IS NULL )
		      AND ( personne.fctorgid = transaction.fctorgid OR transaction.fctorgid IS NULL AND personne.fctorgid IS NULL )
		      AND manif.date >= now()
		      AND manif.id = manifid
		      AND transaction.id NOT IN ( SELECT transaction FROM preselled ) ";
	*/
	
	$query  = ' SELECT DISTINCT r.transaction, p.*
	            FROM reservation_pre r, transaction t
	            LEFT JOIN personne_properso p ON p.id = t.id AND ( t.fctorgid = p.fctorgid OR t.fctorgid IS NULL AND p.fctorgid IS NULL )
	            WHERE r.id NOT IN ( SELECT resa_preid FROM reservation_cur WHERE NOT canceled )
	              AND r.transaction = t.id';
	includePage("late");
?>
