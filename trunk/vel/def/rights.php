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
	require("../conf.inc.php");
	includeClass("bdRequest");
	$user = &$_SESSION["user"];
	$class = "vel";
	$onglet = "Accès des billetteries en ligne";
	$titre = "Gestion des accès à l'interface webservices de vente en ligne.";
	$css    = array("styles/main.css","sco/styles/main.css","def/styles/main.css");
	
	includeLib("headers");
	
	// le formulaire a été soumis
	if ( isset($_POST["submit"]) )
	{
		// il y a un  nouvel enregistrement
		if ( intval($_POST["new"]["accountid"]) > 0 && $_POST["new"]["ip"] && $_POST['new']['salt'] )
		{
		  $arr['ip'] = $_POST['new']['ip'];
			$arr["accountid"] = intval($_POST["new"]["accountid"]);
			$arr["salt"] = $_POST["new"]["salt"];
			if ( !$bd->addRecord("authentication",$arr) )
				$user->addAlert("Impossible d'ajouter votre sélection.");
		}
		
		// suppressions
		$ok = true;
		if ( is_array($_POST["del"]) )
		foreach ( $_POST["del"] as $id )
			$ok = $ok && $bd->delRecordsSimple("authentication",array("id" => intval($id)));
		if ( !$ok ) $user->addAlert("Impossible de supprimer au moins une de vos entrées.");
	}
	
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a href="def/">Paramétrage</a><a href="." class="parent">..</a></p>
<div class="body">
<h2><?php echo htmlsecure($titre) ?></h2>
<form name="formu" method="post" action="<?php echo $_SERVER["PHP_SELF"] ?>">
	<p class="new">
	  <span class="ip" title="Adresse IP"><input type="text" name="new[ip]" value="" /></span>
		<span class="user"><?php
			echo '<select name="new[accountid]">';
			echo '<option value="">-Les comptes-</option>';
			
			$query	= " SELECT *
				    FROM account
				    ORDER BY name";
			$request = new bdRequest($bd,$query);
			
			while ( $rec = $request->getRecordNext() )
				echo '<option value="'.intval($rec["id"]).'">'.htmlsecure($rec["name"]).' ('.htmlsecure($rec["login"]).')</option>';
			
			$request->free();
			echo '</select>';
		?></span>
		<span class="salt" title="Authentification du logiciel distant"><input type="text" name="new[salt]" value="" /></span>
	</p>
	<?php
		$query	= " SELECT auth.id, auth.ip, account.login, account.name, auth.salt
			    FROM authentication auth
			    LEFT JOIN account ON account.id = auth.accountid
			    ORDER BY account.login";
		$request = new bdRequest($bd,$query);
		
		while ( $rec = $request->getRecordNext() )
		{
	?>
	<p class="old">
		<span class="del"><input type="checkbox" name="del[]" value="<?php echo intval($rec["id"]) ?>" /></span><span class="desc">Retirer les droits de cette entrée</span>
		<span class="ip"><?php echo htmlsecure($rec["ip"]) ?></span> | 
		<span class="user"><?php echo htmlsecure($rec["name"].' ('.$rec["login"].')') ?></span> |
		<span class="salt"><?php echo htmlsecure($rec["salt"]) ?></span>
	</p>
	<?php	} ?>
	<hr/>
	<p class="valid"><input type="submit" name="submit" value="Valider" /></p>
</form>
</div>
<?php
	$bd->free();
	includeLib("footer");
?>
