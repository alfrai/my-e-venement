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
	require("inc/vel.php");
	
	$total = 0;
	echo '<ul id="cart">';
	if ( is_array($data["manifs"]) && count($data["manifs"]) > 0 )
	foreach ( $data["manifs"] as $manif )
	{
		echo '<li>';
		echo '<a name="manif_'.intval($manif["manifid"]).'"></a>';
		require("cmdmanif.php");
		echo '</li>';
	}
	
	echo '<li class="total">Total: '.$total.'€</li>';
	
	echo '</ul>';
?>
<p id="cmd"><?php
	if ( !$data["cmd"] )
		echo '<a href="cart.php?cmd">Commander ...</a>';
	elseif ( $data["loggedin"] )
		echo '<a href="cart.php?pay">Payer ...</a>';
?></p>
<?php require("habillage.php"); ?>
<?php require("inc/footers.php"); ?>
