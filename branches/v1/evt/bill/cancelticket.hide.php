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
	
	$manifid = intval($_GET["manifid"]);
	$transac = intval($_GET["transac"]);
	$resa	 = preg_tarif($_GET["resa"]);
	
	$cond	= "    reservation_pre.manifid = ".$manifid."
		   AND reservation_pre.transaction = '".$transac."'
		   AND reservation_pre.reduc = ".$resa["reduc"]."
		   AND reservation_pre.tarifid = tarif.id
		   AND tarif.key = '".$resa["tarif"]."'";
	
	if ( !isset($_GET["delcmd"]) && !isset($_GET["delres"]) )
	{
		$cond = "    resa_preid = reservation_pre.id AND ".$cond;
		$from = array("reservation_pre","tarif");
		
		$bd->beginTransaction();
		echo $bd->updateRecords("reservation_cur",$cond,array("canceled" => "t"),$from)
		   ? "true"
		   : "false";
		$bd->endTransaction();
	}
	else
	{
		$ok = true;
		$from = "tarif";
		$bd->beginTransaction();
		
		if ( isset($_GET["delres"]) )
		{
			$using = array($from,"reservation_pre");
			$tmp = $cond." AND resa_preid = reservation_pre.id";
			$ok = $bd->delRecords("reservation_cur",$tmp,$using);
		}
		
		$cond = "    reservation_pre.id NOT IN ( SELECT resa_preid FROM reservation_cur ) AND ".$cond;
		echo $bd->delRecords("reservation_pre",$cond,$from) && $ok ? "true" : "false";
		
		$bd->endTransaction();
	}
	
	$bd->free();
?>
