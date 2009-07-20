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
	$class .= " factures";
	$subtitle = "Factures éditées";
	$query  = " SELECT transaction.id AS transaction, pers.*, facture.id AS factureid
		    FROM facture, transaction, personne_properso AS pers
		    WHERE facture.transaction = transaction.id
		      AND ( transaction.personneid = pers.id OR transaction.personneid IS NULL AND pers.id IS NULL )
		      AND ( transaction.fctorgid = pers.fctorgid OR transaction.fctorgid IS NULL AND pers.fctorgid IS NULL )";
	$order	= " ORDER BY factureid DESC, nom, prenom, orgnom, transaction";
	
	includePage("late");
?>
