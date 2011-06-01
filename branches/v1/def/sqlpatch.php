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
	$onglet = "Patch SQL";
	$titre = "Assistant de mise à jour de la base de données";
	$class = "sqlpatch";
	$level = 20;
	
	require("conf.inc.php");
	includeLib("actions");

	includeLib("ttt");
	includeJS("ttt");
	includeClass("bdRequest");
	
	$bd     = new bd (      $config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	// execution du patch
	if ( is_array($_FILES["fichier"]) && isset($_POST["check"]) )
	if ( !$_FILES["fichier"]["error"] )
	{
		$resource = @fopen($_FILES["fichier"]["tmp_name"], "r");
		$query = "";
		$buf = true;
		
		if ( $resource )
		while ( $buf )
		{
			$buf = fgets($resource);
			$query .= $buf;
		}
		
		$request = new bdRequest($bd,$query);
		if ( $request->hasFailed() )
			$user->addAlert("Erreur dans l'application du patch");
		else	$user->addAlert("Patch correctement appliqué");
		$request->free();
		
		@fclose($resource);
	}
	
	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a href="def/">Paramétrage</a><a class="nohref active"><?php echo htmlsecure($onglet) ?></a><a href="." class="parent">..</a></p>
<div class="body">
<h2><?php echo htmlsecure($titre) ?></h2>
<p>Attention, l'opération qui vous est proposée peut être très dangereuse. Ne validez cette opération qu'en étant véritable sûr de vos actes.</p>
<form name="formu" method="post" action="<?php echo $_SERVER["PHP_SELF"] ?>" enctype="multipart/form-data">
	<p>
		<span class="label">Patch à appliquer</span>
		<span class="file"><input type="file" name="fichier" size="30" /></span>
	</p>
	<p>
		<span class="submit"><input type="submit" name="submit" value="patcher" /></span>
	</p>
	<p>
		<span class="verif"><input type="checkbox" name="check" value="" /> vous êtes sûr d'utiliser le fichier cité pour mettre la base à jour !</span>
	</p>
	<p class="query"><?php if ( $query ) { ?>
		<textarea name="query" disabled="disabled" rows="10" cols="75"><?php echo htmlsecure($query); ?></textarea>
	<?php } ?></p>
</form>
</div>
<?php
	$bd->free();
	includeLib("footer");
?>
