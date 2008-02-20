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
	includeClass("bdRequest");
	$class = "pro";
	$onglet = "Généralités";
	$titre = 'Paramétrage général de la partie "professionnels".';
	
	includeLib("headers");
	
	// le formulaire a été soumis
	if ( isset($_POST["submit"]) )
	{
		// il y a un  nouvel enregistrement
		if ( $_POST["new"]["name"] != "" )
		{
			$arr["name"] = $_POST["new"]["name"];
			$arr["value"] = $_POST["new"]["value"];
			if ( !$bd->addRecord("params",$arr) )
			{
				$user->addAlert("Impossible d'ajouter votre paramètre.");
				$user->addAlert("Peut-être celui-ci existe déjà en base (vérifier dans la liste) ?");
			}
		}
		
		// il y a des modifs à faire
		$ok = true;
		if ( is_array($_POST["params"]) )
		foreach ( $_POST["params"] as $name => $value )
			$ok = $ok && $bd->updateRecordsSimple("params",array("name" => $name),array("value" => $value));
		if ( !$ok ) $user->addAlert("Impossible de mettre à jour au moins un de vos paramètres.");
		
		// suppressions
		$ok = true;
		if ( is_array($_POST["del"]) )
		foreach ( $_POST["del"] as $name )
			$ok = $ok && $bd->delRecordsSimple("params",array("name" => $name));
		if ( !$ok ) $user->addAlert("Impossible de supprimer au moins une de vos entrées.");
	}
	
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a href="pro/def/">Paramétrage</a><a class="nohref active"><?php echo htmlsecure($onglet) ?></a><a href="." class="parent">..</a></p>
<div class="body">
<h2><?php echo htmlsecure($titre) ?></h2>
<form name="formu" method="post" action="<?php echo $_SERVER["PHP_SELF"] ?>">
	<p class="new">
		<span class="user"><input type="text" name="new[name]" value="" />: <input type="text" name="new[value]" value="" /></span>
	</p>
	<?php
		$query	= " SELECT *
			    FROM params
			    ORDER BY name";
		$request = new bdRequest($bd,$query);
		
		while ( $rec = $request->getRecordNext() )
		{
	?>
	<p class="old">
		<span class="del"><input type="checkbox" name="del[]" value="<?php echo htmlsecure($rec["name"]) ?>" /></span><span class="desc">Supprimer ce paramètre</span>
		<span class="name"><?php echo htmlsecure($rec["name"]) ?></span>
		<span class="value"><input type="text" name="params[<?php echo htmlsecure($rec["name"]) ?>]" value="<?php echo htmlsecure($rec["value"]) ?>" /></span>
	</p>
	<?php	} ?>
	<p class="valid"><input type="submit" name="submit" value="Valider" /></p>
	<hr />
	<p class="infos">
		Les paramétres suivants sont nécessaires au bon fonctionnement du module&nbsp;:
		<ul>
			<li>datemin: 1 paramètre pour définir la date de départ des manifestations</li>
			<li>datemax: 1 paramètre pour définir la date de fin des manifestations</li>
			<li>tarifpros: 1 paramètre pour définir le nom du tarif associé aux pros</li>
		</ul>
	</p>
</form>
</div>
<?php
	$bd->free();
	includeLib("footer");
?>
