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
	
	$class = "vel";
	$onglet = "Tarifs";
	$titre = 'Quels tarifs pour la vente en ligne ?';
	$table = "tarif";
	
	includeLib("headers");
	
	// la suppression de tarifs
	if ( is_array($tarifs = $_POST["todel"]) )
	{
		$nbdel = 0;
		foreach ( $tarifs as $tarif )
			$nbdel += $bd->delRecordsSimple("tariftosell",array("id" => intval($tarif)));
		$user->addAlert($nbdel." tarif(s) supprimé(s)");
	}
	
	// MAJ de leur priorité
	if ( is_array($_POST["priority"]) && is_array($_POST["origprio"]) )
	{
		$nbmaj = 0;
		foreach ( $_POST["priority"] as $tarifid => $priority )
		if ( intval($_POST["origprio"][intval($tarifid)]) != intval($priority) )
			$nbmaj += $bd->updateRecordsSimple("tariftosell",array("id" => intval($tarifid)),array("priority" => intval($priority)));
		$user->addAlert($nbmaj." priorité(s) modifiée(s)");
	}
	
	// l'ajout de tarifs
	if ( is_array($tarifs = $_POST["tarifs"]) )
	{
		$nbnew = 0;
		foreach ( $tarifs as $tarif )
			$nbnew += $bd->addRecord("tariftosell",array("id" => intval($tarif)));
		$user->addAlert($nbnew." tarif(s) ajouté(s)");
	}
	
	// l'affichage des tarifs inclus
	$query	= " SELECT ".$table.".*, tts.priority
		    FROM ".$table.", tariftosell AS tts
		    WHERE tts.id = ".$table.".id
		    ORDER BY priority DESC, key, date";
	$tarifs = new bdRequest($bd,$query);
	
	// l'affichage des tarifs ajoutables
	$query	= " SELECT *
		    FROM ".$table."
		    WHERE date IN (SELECT MAX(date) FROM ".$table." AS tmp WHERE key = tarif.key)
		      AND desact = false
		      AND id NOT IN (SELECT id FROM tariftosell)
		    ORDER BY key";
	$request = new bdRequest($bd,$query);
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a href="vel/def/">Paramétrage</a><a class="nohref active"><?php echo htmlsecure($onglet) ?></a><a href="." class="parent">..</a></p>
<div class="body">
<h2><?php echo htmlsecure($titre) ?></h2>
<form name="formu" method="post" action="<?php echo htmlsecure($_SERVER["PHP_SELF"]).'?'.htmlsecure(isset($_GET["bydate"]) ? "bydate" : "") ?>">
	<ul id="selected"><?php
		while ( $rec = $tarifs->getRecordNext() )
			echo '<li>'.
				'<input type="checkbox" name="todel[]" value="'.intval($rec["id"]).'" /> '.
				htmlsecure($rec["key"].' - '.$rec["description"]).
				' <input type="text" name="priority['.intval($rec["id"]).']" value="'.intval($rec["priority"]).'" />'.
				' <input type="hidden" name="origprio['.intval($rec["id"]).']" value="'.intval($rec["priority"]).'" />'.
				'</li>';
	?></ul>
	<p class="submit"><input type="submit" name="submit" value="ok" /></p>
	<p class="new">
		<select name="tarifs[]" multiple="multiple"><?php
			while ( $rec = $request->getRecordNext() )
				echo '<option value="'.intval($rec["id"]).'" '.($rec["selected"] == 't' ? 'selected="selected"' : '').'>'.
					htmlsecure($rec["key"].' ('.$rec["description"].')').
					'</option>';
		?></select>
	</p>
	<p class="submit"><input type="submit" name="submit" value="ok" /></p>
</form>
</div>
<?php
	$tarifs->free();
	$request->free();
	$bd->free();
	includeLib("footer");
?>
