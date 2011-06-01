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
	global $bd, $config;
	global $id;	// organisme.id;
	
	$orgid = $id;
	
	$query	= " SELECT *
		    FROM billeterie.evenement_categorie
		    WHERE organisme1 = ".$orgid."
		       OR organisme2 = ".$orgid."
		       OR organisme3 = ".$orgid;
	$request = new bdRequest($bd,$query);
	
	if ( $request->countRecords() > 0 )
	{
?>
	<div class="more">
		<p class="titre">Créations</p>
		<ul><?php
			while ( $rec = $request->getRecordNext() )
				echo '<li><a href="evt/infos/fiche.php?id='.intval($rec["id"]).'&view">'.htmlsecure($rec["nom"]).'</a> ('.htmlsecure($rec["typedesc"] ? $rec["typedesc"] : $rec["catdesc"]).')</li>';
		?></ul>
	</div>
<?php
	}
	$request->free();
?>
