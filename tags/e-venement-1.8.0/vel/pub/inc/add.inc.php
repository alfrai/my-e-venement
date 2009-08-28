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
*    Copyright (c) 2006-2008 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	// préconditions
	$error = true;
	if ( is_array($_POST["qty"]) && intval($_POST["manifid"]) > 0 )
	{
		// condition permettant l'ajout de ces places au panier
		$query = " SELECT jauge - selled AS free FROM maniftosell WHERE id = ".intval($_POST["manifid"]);
		$request = new bdRequest($bd,$query);
		if ( $request->countRecords() > 0 )
		{
			$free = intval($request->getRecord("free"));
			$error = false;
		}
		$request->free();
	}
	
	if ( $error )
	{
		$nav->addAlert("Impossible de réserver ces places");
		$nav->redirect( $_SERVER["HTTP_REFERER"] ? $_SERVER["HTTP_REFERER"] : "." );
	}
	
	// RAZ de la manifestation
	unset($_SESSION["vel"]["cart"][intval($_POST["manifid"])]);
	$_SESSION["vel"]["cart"][intval($_POST["manifid"])] = array();
	
	foreach ( $_POST["qty"] as $tarifid => $qty )
	{
		// ajout au panier
		$qty = intval($qty);	// nb direct
		if ( $qty > 0 )
		{
			if ( $qty > $free )
			{
				$nav->addAlert("La jauge n'est pas assez grande, il vous manquera ".($qty - $free)." place(s).");
				$qty = $free;
			}
			$_SESSION["vel"]["cart"][intval($_POST["manifid"])][intval($tarifid)] = $qty;
		}
	}
	
	$redirectnewurl = "cart.php#manif_".intval($_POST["manifid"]);	
?>
