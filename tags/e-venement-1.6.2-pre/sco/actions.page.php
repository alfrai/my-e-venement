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
	global $config, $id, $user;
?>
<p class="actions">
<?php
	echo '<a href="'.($href = "sco/index.php").'" class="'.($_SERVER["PHP_SELF"] == $config["website"]["root"].$href ? "active" : "") .'">Index</a>';
	
	$href = "sco/fiche.php";
	echo '<a '.($user->scolevel >= $config["sco"]["right"]["mod"] ? 'href="'.$href.'?add"' : '').'
		 class="'.($_SERVER["PHP_SELF"] == $config["website"]["root"].$href && isset($_GET["add"]) ? "active" : "") .' add"
		 >Créer</a>';
	echo '<a '.(intval($id) != 0 ? 'href="'.$href.'?id='.$id.'"' : '').'
		 class="'.($_SERVER["PHP_SELF"] == $config["website"]["root"].$href && !isset($_GET["add"]) && !isset($_GET["del"]) ? "active" : "") .' '.(intval($id) == 0 ? "inactive" : "").'"
		 >Consulter</a>';
	echo '<a '.(($ok = intval($id) != 0 && $user->scolevel >= $config["sco"]["right"]["mod"]) ? 'href="'.$href.'?id='.$id.'&del"' : '').'
		 class="'.($_SERVER["PHP_SELF"] == $config["website"]["root"].$href && isset($_GET["del"]) ? "active" : "") .' '.(!$ok ? "inactive" : "").'"
		 >Supprimer</a>';
	
	if ( $user->scolevel >= $config["sco"]["right"]["param"] )
	{
		echo '<a href="'.($href = "sco/def/").'" class="add ';
		echo ($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "");
		echo '">Paramétrage</a>';
	}
	
	echo '<a href="evt/" class="parent">..</a>';
?>
</p>
