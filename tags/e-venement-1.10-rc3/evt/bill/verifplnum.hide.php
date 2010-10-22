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
	$jauge = true;
	
	if ( intval($_GET["manifid"]) > 0 && intval($_GET["plnum"]) > 0 )
	{
		// il faut que la place demandée soit dans les places dispo pour la manif
		// il faut qu'elle ait été demandé un nombre pair de fois (0, 2, 4, ...) puisqu'avec la contrainte
		// sur reservation_pre, ca garantit que la place est libre avec les annulations relatives
		$query	= " SELECT 
			     (SELECT count(*) > 0
			      FROM manifestation_plnum
			      WHERE manifestationid = ".intval($_GET["manifid"])."
			        AND plnum = ".intval($_GET["plnum"]).")
			    AND
			     (SELECT SUM((NOT annul)::integer*2-1) = 0
			      FROM (SELECT 1 as num, annul FROM reservation_pre WHERE manifid = ".intval($_GET["manifid"])." AND plnum = ".intval($_GET["plnum"])." AND NOT id = ".intval($_GET["id"])."
			            UNION
			            SELECT 2 AS num, true AS annul
			            UNION
			            SELECT 3 AS num, false AS annul) AS tmp)
			   AS ok";
		$request = new bdRequest($bd,$query);
		if ( $request->getRecord("ok") == 't' )
			echo "true";
		else	echo "false";
		$request->free();
	}
	else echo "false";
	
	$bd->free();
?>
