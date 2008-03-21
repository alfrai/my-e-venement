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
	
	$seeall = $_GET["seeall"] == "yes";
	
	if ( $_GET["flashdate"] )
	$flashdate = $_GET["flashdate"];
	
	$query	= "SELECT pers.*, transaction, prix AS topay,
			 (SELECT sum(paiement.montant) AS prix FROM paiement WHERE transaction = topay.transaction ".(isset($flashdate) ? "AND date <= '".pg_escape_string($flashdate)."'::date" : "" )." GROUP BY transaction) AS paid
		   FROM personne_properso AS pers, transaction,
		   	(SELECT resa.transaction, sum(getprice(resa.manifid, resa.tarifid)::double precision * (- 1::double precision) * (resa.annul::integer * 2 - 1)::double precision) AS prix
		   	 FROM reservation_cur AS cur, reservation_pre AS resa
		   	 WHERE NOT cur.canceled
		   	   AND cur.resa_preid = resa.id
		   	   ".(isset($flashdate) ? "AND cur.date <= '".pg_escape_string($flashdate)."'::date" : "" )."
		   	 GROUP BY resa.transaction) AS topay
		   WHERE topay.transaction = transaction.id
		     AND ( transaction.personneid = pers.id
		       OR (transaction.personneid IS NULL AND pers.id IS NULL) )
		     AND ( transaction.fctorgid = pers.fctorgid
		       OR (transaction.fctorgid IS NULL AND pers.fctorgid IS NULL) )";
	if ( !$seeall ) $query .= " AND
				    ( topay.prix - (SELECT sum(paiement.montant) AS prix FROM paiement WHERE transaction = topay.transaction AND date <= '".pg_escape_string($flashdate)."'::date GROUP BY transaction) > 0
				      OR (topay.transaction NOT IN (SELECT transaction FROM paiement) AND topay.prix > 0 ))";
	//			    SELECT prix FROM paid WHERE paid.transaction = topay.transaction) > 0
	$class .= " credit";
	$subtitle = "Personnes n'ayant pas réglé la totalité de leurs créances";
	$credit = true;
	
	echo $query;
	
	$flashdate = true;
	includePage("late");
	
	$query = "DROP TABLE etatcompte";
	$request = new bdRequest($bd,$query);
	$request->free();
?>
