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
	global $bd,$user,$nav,$class,$title,$subtitle,$stage,$default,$numtransac,$cantgetback,$spectateur;
	includeClass("bdRequest/array");
	includeLib("login-check");
	includeLib("jauge");
	includeJS("bill","evt");
	includeJS("ttt");
	includeJS("annu");
	includeJS("ajax");
	
	includeLib("headers");
	$jauge = true;
	$action = $actions["add"];
	$manif = is_array($_POST["manif"]) ? $_POST["manif"] : array();
	
	if ( count($manif) > 0 && !isset($_POST["client"]) )
		$user->addAlert("Il faut sélectionner une personne pour passer à l'étape suivante");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
<?php
	if ( isset($subtitle) ) echo '<h2>'.htmlsecure($subtitle).'</h2>';
	includePage("vdir-stages");
?>
<form name="formu" class="spectacles" method="post" action="<?php echo $_SERVER["PHP_SELF"] ?>">
	<fieldset class="hidden">
		<input type="hidden" name="numtransac" value="<?php echo $numtransac ?>" />
		<input type="hidden" id="desc" name="desc" value="<?php
			echo htmlsecure("Pour plus d'aisance, cliquer sur ce lien de manière à l'ouvrir dans un nouvel onglet... (ctrl+clic)")
			?>" />
	</fieldset>
	
	<?php if ( !$cantgetback ) { ?>
	<h2>Reprendre une opération</h2>
	<div>
	<p class="transaction">
		numéro de transaction:
		#<input	type="text" name="oldtransac" value="" id="focus" />
	</p>
	</div>
	<?php } ?>
	
	<p class="valid">
		<input type="button" onclick="javascript: window.history.back();" disabled="disabled" class="back" value="<< Revenir" name="back" />
		<input type="submit" class="next" value="Suivant >>" name="submit" />
	</p>
</form>
</div>
<?php
	includeLib("footer");
?>
