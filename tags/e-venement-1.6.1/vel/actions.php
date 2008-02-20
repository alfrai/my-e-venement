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
	
?> <p class="actions"> <?php
	echo '<a href="'.($href = "pro/index.php").'" class="';
	echo ($config["website"]["root"].$href == $_SERVER["PHP_SELF"] || $config["website"]["root"]."pro/pro.php" == $_SERVER["PHP_SELF"] ? "active " : "");
	echo '">Personnes</a>';
	echo '<a href="'.($href = "pro/manifs.php").'" class="';
	echo ($config["website"]["root"].$href == $_SERVER["PHP_SELF"] || $config["website"]["root"]."pro/manif.php" == $_SERVER["PHP_SELF"] ? "active" : "");
	echo '">Manifestations</a>';
	
	echo '<span class="space">:</span>';
	$fiche = $config["website"]["root"]."pro/manifs.php" == $_SERVER["PHP_SELF"] || $config["website"]["root"]."pro/index.php" == $_SERVER["PHP_SELF"];
	echo '<a ';
	switch ( $_SERVER["PHP_SELF"] ) {
	case $config["website"]["root"]."pro/index.php":
		echo 'href="ann/fiche.php?add"';
		break;
	case $config["website"]["root"]."pro/manifs.php":
		echo 'href="evt/infos/fiche.php?add"';
		break;
	default:
		echo 'class="disabled"';
		break;
	}
	echo '>Ajouter</a>';
	echo '<span class="space">:</span>';
	
	echo '<a href="'.($href = "pro/unpaid.php").'" class="';
	echo ($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "");
	echo '">Impayés</a>';
	echo '<a href="'.($href = "pro/export.php").'" class="';
	echo ($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "");
	echo '">Exports</a>';
	
	if ( $user->prolevel >= $config["pro"]["right"]["param"] )
	{
		echo '<a href="'.($href = "pro/def/").'" class="add ';
		echo ($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "");
		echo '">Paramétrage</a>';
	}
	
	echo '<a href="evt/" class="parent">..</a>';
?>
</p>
