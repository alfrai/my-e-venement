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
<form id="addmanif" action="add.php" method="post"><?php
	echo '<p class="nom"><a href="evt.php?id='.intval($manif["id"]).'">'.htmlsecure($manif["nom"]).'</a></p>';
	
	echo '<div class="manif">';
	
	echo '<input type="hidden" name="manifid" value="'.intval($manif["manifid"]).'" />';
	
	echo '<p class="date">le '.date($config["format"]["date"].' à '.$config["format"]["maniftime"],strtotime($manif["date"])).'</p>';
	echo '<p class="site">';
	echo '<span class="ville">'.htmlsecure($manif["ville"]).'</span>, ';
	echo '<span class="nom">'.htmlsecure($manif["sitenom"]).'</span>';
	echo '</p>';
	
	$totalmanif = 0;
	$tarifs = array();
	if ( is_array($manif["qty"]) )
	foreach ( $manif["qty"] as $tarifid => $qty )
	if ( intval($tarifid) > 0 )
	{
		// tarifs existants ds la commande
		echo '<p class="mod">';
		echo '<span class="qty">';
		echo $data["cmd"] ? intval($qty) : '<input type="text" name="qty['.intval($tarifid).']" value="'.intval($qty).'" />';
		echo '</span> ';
		echo '<span class="tarif">';
		if ( basename($_SERVER["PHP_SELF"],'.php') == 'cart' )
			echo htmlsecure($data["tarifs"][intval($tarifid)]["description"]);
		else
		{
			echo '	<select name="tarif" onchange="javascript: '."this.parentNode.parentNode.getElementsByTagName('input').item(0).name='qty['+this.value+']';".'">';
			foreach ( $data["tarifs"] as $tarif )
				echo '<option value="'.intval($tarif["id"]).'" '.($data["tarifs"][0] == $tarif ? 'selected="selected"' : '').'>'.htmlsecure($tarif["description"]).'</option>';
			echo '</select>';
		}
		echo '</span>';
		$totalmanif += $buf = $manif["pu"][intval($tarifid)] * intval($qty);
		echo '<span class="total">'.round($buf,2).'€</span>';
		echo '</p>';
		
		// on enregistre le tarif pour ne pas qu'il soit réutilisable comme "nouveau"
		if ( !in_array(intval($tarifid), $tarifs) )
		$tarifs[] = intval($tarifid);
	}
	
	if ( !$data["cmd"] )
	{
		// nettoyage des tarifs encore dispo
		$tmptarifs = array();
		foreach ( $data["tarifs"] as $tarifid => $tarif )
		if ( !in_array(intval($tarifid),$tarifs) )
			$tmptarifs[] = $tarif;
		
		// nouveau tarif
		if ( count($tmptarifs) > 0 )
		{
			echo '<p class="add">';
			echo '<span class="qty">
				<input type="text" name="qty['.intval($tmptarifs[0]["id"]).']" value="" />
			      </span>';
			echo '<span class="tarif">';
			echo '	<select name="tarif" onchange="javascript: '."this.parentNode.parentNode.getElementsByTagName('input').item(0).name='qty['+this.value+']';".'">';
			foreach ( $tmptarifs as $tarif )
				echo '<option value="'.intval($tarif["id"]).'">'.htmlsecure($tarif["description"].' ('.$manif["pu"][intval($tarif["id"])].'€)').'</option>';
			echo '</select>';
			echo '</p>';
		}
		
		echo '<p class="submit"><input type="submit" name="add" value="ok" /></p>';
		
	} // if ( !$data['cmd'] )
	
	$total += $totalmanif;
	echo '<p class="total">'.round($totalmanif,2).'€</p>';
	
	echo '</div>';
?></form>
