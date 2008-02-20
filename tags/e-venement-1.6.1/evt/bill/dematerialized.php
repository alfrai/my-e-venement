<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    beta-libs is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with beta-libs; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	require("conf.inc.php");
	$class .= "dematerialized";
	
	if ( $user->evtlevel < $config["evt"]["right"]["mod"] )
	{
		$user->addAlert($msg = "Vous n'avez pas un niveau de droits suffisant pour accéder à cette fonctionnalité");
		$nav->redirect($config["website"]["base"]."evt/bill/",$msg);
	}

	
	includeLib("headers");
	
	$bd->beginTransaction();
	
	// on annule les précédentes impressions
	$bd->updateRecords("reservation_cur","resa_preid = reservation_pre.id AND reservation_pre.transaction = '".pg_escape_string($_GET["t"])."'",array("canceled" => "t"),"reservation_pre");
	
	// on ajoute de nouvelles impressions
	$fields = array("accountid","resa_preid");
	$query  = "SELECT ".$user->getId()." AS accountid, resa.id AS resa_preid
		   FROM reservation_pre AS resa
		   WHERE transaction = '".pg_escape_string($_GET["t"])."'";
	if ( $bd->addRecordsQuery("reservation_cur",$fields,$query) > 1 )
	$bd->updateRecordsSimple("transaction",array("id" => $_GET["t"]),array("dematerialized" => "t"));	// on définit la transaction comme "dématerialisée"
	
	$bd->endTransaction();
?>
<!--<script type="text/javascript">window.print(); window.close();</script>-->
<?php
	includeLib("footer");
	$bd->free();
?>
