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
	includeLib("jauge");
	$jauge = true;
	
	/*
	$query  = ' SELECT ppp.orgnom, ppp.nom, ppp.prenom, c.transaction, count(p.*) AS nb
	            FROM reservation_pre p, contingeant c
	                 LEFT JOIN personne_properso ppp
	                 ON     ppp.id = c.personneid
	                    AND ( ppp.fctorgid = c.fctorgid OR ppp.fctorgid IS NULL AND c.fctorgid IS NULL )
	            WHERE p.transaction = c.transaction
	              AND manifid = '.intval($_GET["manifid"]).'
	            GROUP BY ppp.orgnom, ppp.nom, ppp.prenom, c.transaction
	            ORDER BY ppp.orgnom, ppp.nom, ppp.prenom, c.transaction';
	*/
	$query  = ' SELECT o.nom AS orgnom, p2.nom, p2.prenom, c.transaction, count(p.*) AS nb
	            FROM reservation_pre p, contingeant c
	            LEFT JOIN personne p2 ON p2.id = c.personneid
	            LEFT JOIN org_personne op ON op.personneid = p2.id AND op.id = c.fctorgid
	            LEFT JOIN organisme o ON o.id = op.organismeid
	            WHERE p.transaction = c.transaction
	              AND manifid = '.intval($_GET["manifid"]).'
	            GROUP BY p2.nom, p2.prenom, o.nom, c.transaction
	            ORDER BY p2.nom, p2.prenom, o.nom, c.transaction';
	$request = new bdRequest($bd,$query);
	$contingents = array();
	while ( $rec = $request->getRecordNext() )
	  $contingents[$rec['transaction']] = array(
	    'orgnom' => $rec['orgnom'],
	    'nom'    => $rec['nom'],
	    'prenom' => $rec['prenom'],
	    'nb'     => $rec['nb'],
	  );
	$request->free();
	
	$query	= " SELECT *
		    FROM info_resa AS manif
		    WHERE manifid = ".intval($_GET["manifid"]);
  if ( $config['evt']['spaces'] )
    $query .= ' AND spaceid '.($user->evtspace ? '= '.$user->evtspace : 'IS NULL');
	$request = new bdRequest($bd,$query);
	
	if ( $rec = $request->getRecord() )
		printJauge(intval($rec["jauge"]),intval($rec["preresas"]),intval($rec["resas"]),450,intval($rec["commandes"]),550,null,$contingents);

	$request->free();
	$bd->free();
?>
