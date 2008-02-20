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
?>
<form id="client" action="?re">
	<p id="indb">
		<span class="email">email: <input type="text" name="email" value="" /></span>
		<span class="passwd">passwd: <input type="text" name="passwd" value="" /></span>
	</p>
	<p id="register">
		<span class="nom">Titre - Nom - Prénom:
			<input type="text" name="titre" value="" />
			<input type="text" name="nom" value="" />
			<input type="text" name="prenom" value="" />
		</span>
		<span class="addr">Rue/chemin/lieu-dit: <textarea name="adresse"></textarea></span>
		<span class="ville">CP - Ville:
			<input type="text" name="cp" value="" />
			<input type="text" name="ville" value="" />
		</span>
		<span class="tel">Téléphone: <input type="text" name="telephone" value="" /></span>
		<span class="email">email: <input type="text" name="email" value="" /></span>
		<span class="passwd">passwd: <input type="password" name="passwd" value="" /></span>
	</p>
</form>
<?php require("habillage.php"); ?>
<?php require("inc/footers.php"); ?>
