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
	global $title;
	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php includePage("actions"); ?>
<div class="body">
<h2>Supprimer l'entrée #<?php echo intval($_GET["id"]) ?></h2>
<form name="formu" class="del" method="get" action="<?php echo htmlsecure($_SERVER["PHP_SELF"]); ?>">
	<input type="hidden" name="del" value="" />
	<input type="hidden" name="id" value="<?php echo intval($_GET["id"]) ?>" />
	<p>
		Êtes-vous sûr ?
		<input type="submit" name="confirm" value="oui" id="focus" />
		<input type="submit" name="cancel" value="non" />
	</p>
</form>
<?php
	includeLib("footer");
?>
