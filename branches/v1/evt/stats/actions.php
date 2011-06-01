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
	global $config, $fiche, $salle, $id;
	
	$mod = $user->evtlevel >= $config["evt"]["right"]["mod"];
	
	$url = false;
	if ( $evt )	$url = "evt/infos/fiche.php";
	if ( $salle )	$url = "evt/infos/salle.php";
?>
<p class="actions">
<?php
	echo '<a href="'.($href = "evt/stats/index.php").'" class="';
	echo ( $config["website"]["root"]."evt/stats/index.php" == $_SERVER["PHP_SELF"] ? "active" : "");
	echo '">Index</a>';
	
	echo '<a href="'.($href = "evt/stats/personnes.php").'" class="add ';
	echo ( $config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "");
	echo '">Personnes</a>';
	
	echo '<a href="'.($href = "evt/stats/pros.php").'" class="';
	echo ( $config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "");
	echo '">Pros</a>';
	
	echo '<a href="'.($href = "evt/stats/global.php").'" class="';
	echo ( $config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "");
	echo '">Global</a>';
	
	echo '<a href="'.($href = "evt/stats/ventes.php").'" class="add ';
	echo ( $config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "");
	echo '">Ventes</a>';
	echo '<a href="'.($href = "evt/stats/tarifs.php").'" class="';
	echo ( $config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "");
	echo '">Tarifs</a>';
	echo '<a href="'.($href = "evt/stats/jauges.php").'" class="';
	echo ( $config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "");
	echo '">Jauges</a>';
	
	echo '<a href="evt/" class="parent">..</a>';
?>
</p>
