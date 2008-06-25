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
	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a class="active nohref">Paramétrage</a><a href="." class="parent">..</a></p>
<div class="body">
<ul>
	<li>Système
		<ul>
			<li><a href="def/users.php">Administration</a> des comptes et des droits</li>
			<li><a href="def/userlog.php">Journal des connexions</a></li>
			<?php if ( $user->hasRight($config["right"]["devel"]) ) { ?>
			<li><a href="def/sqlpatch.php">Maintenance</a> technique de l'application</li>
			<li><a href="def/archive.php">Archivage</a> et nettoyage des données liées à la billetterie</li>
			<?php } ?>
		</ul>
	</li>
	<li>Données types
		<ul>
			<li><a href="def/catorg.php">Catégories d'organismes</a></li>
			<li><a href="def/fct.php">Les fonctions types</a> au sein des organismes</li>
			<li><a href="def/teltype.php">Les types de téléphones</a> génériques</li>
			<li><a href="def/titretype.php">Les titres</a> génériques (M./Mme/Mlle/...)</li>
		</ul>
	</li>
	<li>Contact / autres
		<ul>
			<li><a target="_blank" href="<?php echo htmlsecure($config["gmap"]["perso_url"]) ?>">Personnaliser sa Google Map</a></li>
		</ul>
	</li>
	<?php
		if ( is_array($config["mods"]) )
		foreach ( $config["mods"] as $mod )
			includePage("../".$mod."/def/index",false);
	?>
</ul>
</div>
<?php
	includeLib("footer");
?>
