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
*    Copyright (c) 2008 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	require("conf.inc.php");
	includeClass("bdRequest");
	$class = "bts";
	$onglet = "BTS";
	$titre = 'Gestion des droits du module de gestion de tickets.';
	
	includeLib("headers");
	
	// le formulaire a été soumis
	if ( isset($_POST["submit"]) )
	{
		// il y a un  nouvel enregistrement
		if ( intval($_POST["new"]["accountid"]) > 0 )
		{
			$request = new bdRequest($bd,"SELECT * FROM fly_users_private LIMIT 0");
			$arr = $request->getFields();
			$request->free();
			
			foreach ( $arr as $key => $value )
				$arr[$key] = $value == "int4" ? 0 : "";
			$arr["notify_type"]	= 3;
			$arr["notify_own"]	= 1;
			$arr["account_enabled"]	= 1;
			$arr["tasks_perpage"]	= 10;
			$arr["time_zone"]	= 1;
			$arr["user_id"] = intval($_POST["new"]["accountid"]);
			if ( !$bd->addRecord("fly_users_private",$arr) )
				$user->addAlert("Impossible d'ajouter votre sélection.");
		}
	}
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a href="def/">Paramétrage</a><a class="nohref active"><?php echo htmlsecure($onglet) ?></a><a href="." class="parent">..</a></p>
<div class="body">
<h2><?php echo htmlsecure($titre) ?></h2>
<form name="formu" method="post" action="<?php echo $_SERVER["PHP_SELF"] ?>">
	<p class="desc">Attention, les comptes ajoutés ne peuvent être désactivés que depuis le module lui-même et ne peuvent en aucun cas être retirés.</p>
	<p class="new">
		<span class="user"><?php
			echo '<select name="new[accountid]">';
			echo '<option value="">-Les comptes-</option>';
			
			$query	= " SELECT *
				    FROM account
				    WHERE id NOT IN ( SELECT user_id FROM fly_users )
				    ORDER BY name";
			$request = new bdRequest($bd,$query);
			
			while ( $rec = $request->getRecordNext() )
				echo '<option value="'.intval($rec["id"]).'">'.htmlsecure($rec["name"]).' ('.htmlsecure($rec["login"]).')</option>';
			
			$request->free();
			echo '</select>';
		?></span>
	</p>
	<?php
		$query	= " SELECT account.id, account.user_name AS login, account.real_name AS name
			    FROM fly_users AS account
			    ORDER BY user_name";
		$request = new bdRequest($bd,$query);

		while ( $rec = $request->getRecordNext() )
		{
	?>
	<p class="old">
		<span class="user"><?php echo htmlsecure($rec["name"].' ('.$rec["login"].')') ?></span>
	</p>
	<?php	} ?>
	<p class="valid"><input type="submit" name="submit" value="Valider" /></p>
</form>
</div>
<?php
	$bd->free();
	includeLib("footer");
?>
