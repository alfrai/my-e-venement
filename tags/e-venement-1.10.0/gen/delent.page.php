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
	require_once("conf.inc.php");
	includeClass("bdRequest");
	includeLib("ttt");
	includeLib("actions");
	
	$action = $actions["del"];
	
	$user->redirectIfNoRight($nav,$config["right"]["del"]);
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	$id = intval($_GET["id"]);
	
	// Suppression de la fiche
	if ( $id > 0 && isset($_GET["oui"]) )
	{
		if ( $bd->delRecordsSimple( $table, array("id" => $id) ) )
			$user->addAlert($msg = "Fiche supprimée");
		else	$user->addAlert($msg = "Echec de suppression de la fiche");
		$nav->redirect($next."?s=".htmlsecure(strtolower(substr($_GET["nom"],0,1))),$msg);
	}

	// préparation du formulaire de la fiche
	if ( $id > 0 && !isset($_GET["non"]) )
	{
		$query	= " SELECT * FROM ".$table." WHERE id = ".$id;
		$request = new bdRequest($bd,$query);
		
		// affichage du formulaire si possible
		if ( $request->countRecords() > 0 )
		{
			includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><?php printActions($baseurl); ?></p>
<div class="body">
<h2>Retirer <?php echo $request->getRecord("titre").' '.$request->getRecord("nom").' '.$request->getRecord("prenom") ?> de l'annuaire ?</h2>
<form name="formu" class="del" method="get" action="<?php echo $_SERVER["PHP_SELF"] ?>">
	<input type="hidden" name="del" value="" />
	<input type="hidden" name="id" value="<?php echo $id ?>" />
	<input type="hidden" name="nom" value="<?php echo $request->getRecord("nom") ?>" />
	<p>
			Êtes-vous sûr ?
			<input type="submit" name="oui" value="oui" id="focus" />
			<input type="submit" name="non" value="non" />
	</p>
</form>
</div>
<?php
			includeLib("footer");
			$request->free();
			$bd->free();
			exit(0);
		}
		$request->free();
	}
	$bd->free();
	$user->addAlert($msg = "Impossible de supprimer la fiche. Elle est introuvable");
	$nav->redirect($next,$msg);
?>
