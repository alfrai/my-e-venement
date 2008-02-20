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
	echo '<a href="'.($href = "evt/infos/index.php").'" class="';
	echo ($config["website"]["root"].$href == $_SERVER["PHP_SELF"]
	   || $config["website"]["root"]."evt/infos/fiche.php" == $_SERVER["PHP_SELF"]
	   || $config["website"]["root"]."evt/infos/manif.php" == $_SERVER["PHP_SELF"] ? "active" : "");
	echo '">Évènements</a>';
	echo '<a href="'.($href = "evt/infos/agenda.php").'" class="'.($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active " : "").'">Agenda</a>';
	echo '<a href="'.($href = "evt/infos/salles.php").'" class="';
	echo ($config["website"]["root"].$href == $_SERVER["PHP_SELF"] || $config["website"]["root"]."evt/infos/plan.php" == $_SERVER["SCRIPT_NAME"] ? "active" : "");
	echo '">Salles</a>';
	
	if ( $config["website"]["root"]."evt/infos/manif.php" == $_SERVER["PHP_SELF"] )
		$moreclass = " manif";
	else	$moreclass = "";
	if ( $url )
	{
		echo '<span class="space">:</span>';
		echo '<a '.( $id ? 'href="'.$url.'?id='.$id.'&view"'.($action == $actions["view"] ? ' class="active'.$moreclass.'"' : "") : 'class="inactive"').'>Consulter</a>';
		if ( $mod )
		{
			echo '<a '.( $id && $config["website"]["root"]."evt/infos/manif.php" != $_SERVER["PHP_SELF"] ? 'href="'.$url.'?id='.$id.'&edit"'.($action == $actions["edit"] ? ' class="active"' : "") : 'class="inactive"').'>Modifier</a>';
			echo '<a '.( $id && $config["website"]["root"]."evt/infos/manif.php" != $_SERVER["PHP_SELF"] ? 'href="'.$url.'?id='.$id.'&del" '.($action == $actions["del"] ? ' class="active"' : "")  : 'class="inactive"').'>Supprimer</a>';
			echo '<a href="'.$url.'?id='.$id.'&add" class="add'.(isset($action) && $action == $actions["add"] ? ' active' : "").'">Créer</a>';
		}
	}
	echo '<span class="space">:</span>';
	echo '<a href="'.($href = "evt/infos/exts.php").'" class="'.($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active " : "").'">Extractions</a>';
	
	echo '<a href="evt/" class="parent">..</a>';
?>
</p>
