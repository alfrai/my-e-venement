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
	foreach ( $data as $evt )
	{
		echo '<div class="evt">';
		echo '<p class="nom"><a href="evt.php?id='.intval($evt["id"]).'">'.htmlsecure($evt["nom"]).'</a></p>';
		echo '<p class="desc">'.htmlsecure($evt["description"]).'</p>';
		
		echo '<p class="ages">';
		$ages = $evt["ages"];
		if ( $ages[0] == 0 )	// pas d'age de début
			echo '<span class="max">jusqu\'à '.floatval($ages[1]).' an(s)</span>';
		elseif ( count($ages) > 1 )		// 2 ages dispo
			echo '<span class="min">de '.floatval($ages[0]).' an(s)</span> à <span class"max">'.floatval($ages[1]).' an(s)</span>';
		elseif ( !isset($ages[1]) )	// pas d'age de fin
			echo '<span class="max">à partir de '.floatval($ages[0]).' an(s)</span>';
		echo '</p>';
			
		
		if ( is_array($evt["manifs"]) )
		foreach ( $evt["manifs"] as $manif )
		{
			echo '<div class="manif">';
			
			echo '<p class="date"><a href="'.htmlsecure($manif["urls"]["addtocart"]).'">le '.date($config["format"]["date"].' à '.$config["format"]["maniftime"],strtotime($manif["date"])).'</a></p>';
			echo '<p class="site">';
			echo '<span class="ville">'.htmlsecure($manif["ville"]).'</span>, ';
			echo '<span class="nom">'.htmlsecure($manif["sitenom"]).'</span>';
			echo '</p>';
			
			echo '</div>';
		}
		
		echo '</div>';
	}
?>
