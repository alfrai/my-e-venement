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
	includeJS("bill","evt");
	includeJS("ajax");
	
	$css[] = "evt/styles/jauge.css";
	$css[] = "vel/styles/jauge.css";
	
	$class = "vel";
	$onglet = "Manifestations";
	$titre = 'Quelles manifestations pour la vente en ligne ?';
	
	includeLib("headers");
	
	// enregistrement des manifs et de leur jauge
	if ( is_array($_POST["jauge"]) )
	{
		$nbupd = 0;
		foreach ( $_POST["jauge"] as $manifid => $jaugetosell )
		if ( !is_array($_POST["todel"]) || !in_array($manifid,$_POST["todel"]) )
			$nbupd += $bd->addOrUpdateRecord("maniftosell", array("id" => $manifid), array("jauge" => $jaugetosell));
		$user->addAlert($nbupd." enregistrements correctement ajoutés ou mis à jour.");
	}
	
	// suppression des manifestations à supprimer
	if ( is_array($_POST["todel"]) )
	{
		$nbdel = 0;
		foreach ( $_POST["todel"] as $delmanif )
		if ( intval($delmanif)."" == $delmanif."" )
			$nbdel += $bd->delRecordsSimple("maniftosell",array("id"=>$delmanif));
		$user->addAlert($nbdel." enregistrements supprimés");
	}
	
	$manifs = is_array($_POST["manifs"]) ? $_POST["manifs"] : array();
	
	$orderby = isset($_GET["bydate"]) ? "date, nom" : "nom, date";
	$query = ' SELECT evt.nom, evt.id AS evtid, categorie, catdesc, metaevt,
			  site.nom AS site, site.ville,
			  manif.date, manif.id, jauge, colors.color, jauge AS jaugetotal,
			  '.($manifs ? 'manif.id IN ('.implode(',',$manifs).')' : 'false').' AS selected,
			  manif.id IN (SELECT id FROM maniftosell WHERE id = manif.id) AS recorded,
			  (SELECT jauge FROM maniftosell WHERE id = manif.id) AS jaugetosell
		   FROM manifestation AS manif, evenement_categorie AS evt, site, colors
		   WHERE manif.evtid = evt.id
		     AND site.id = manif.siteid
		     AND colors.id = manif.colorid
		   ORDER BY selected DESC, recorded DESC, '.$orderby;
	$request = new bdRequest($bd,$query);
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a href="vel/def/">Paramétrage</a><a class="nohref active"><?php echo htmlsecure($onglet) ?></a><a href="." class="parent">..</a></p>
<div class="body">
<h2><?php echo htmlsecure($titre) ?></h2>
<form name="formu" method="post" action="<?php echo htmlsecure($_SERVER["PHP_SELF"]).'?'.htmlsecure(isset($_GET["bydate"]) ? "bydate" : "") ?>">
	<p class="order" title="Attention de valider les jauges au risque de perdre la sélection temporaire"><?php
		echo isset($_GET["bydate"]) ? '<a href="'.htmlsecure($_SERVER["PHP_SELF"]).'">Par nom</a>' : 'Par nom';
		echo ' - ';
		echo isset($_GET["bydate"]) ? 'Par date' : '<a href="'.htmlsecure($_SERVER["PHP_SELF"]).'?bydate">Par date</a>';
	?></p>
	<ul id="selected"><?php
		while ( $rec = $request->getRecordNext() )
		{
			if ( $rec["selected"] == 'f' && $rec["recorded"] == 'f' )
			{
				$request->previousRecord();
				break;
			}
			
			echo '<li class="displayjauge"
				  style="background-color: #'.htmlsecure($rec["color"]).'"
				  onmouseover="javascript: bill_jauge('.intval($rec["id"]).');">';
			
			echo '<p class="content">'.
				'<input type="checkbox" name="todel[]" value="'.intval($rec["id"]).'" /> '.
				htmlsecure($rec["nom"].($rec["selected"] == 't' ? '*' : '').' - '.date($config["format"]["date"].' '.$config["format"]["maniftime"],strtotime($rec["date"])).' - '.$rec["site"].' ('.$rec["ville"].')').
				' - jauge: <input type="text" name="jauge['.intval($rec["id"]).']" value="'.intval($rec["jaugetosell"] ? $rec["jaugetosell"] : $rec["jauge"]).'" /> pl. sur '.intval($rec["jaugetotal"]).' pl.'.
				'</p>';
				
			if ( $jauge || true )
				echo '<p class="jauge"><span id="manif_'.intval($rec["id"]).'"></span></p>';
			
			echo '</li>';
		}
	?></ul>
	<p class="submit"><input type="submit" name="submit" value="ok" /></p>
	<p class="new">
		<select name="manifs[]" multiple="multiple"><?php
			while ( $rec = $request->getRecordNext() )
			{
				echo '<option style="background-color: #'.htmlsecure($rec["color"]).'" value="'.intval($rec["id"]).'">'.
					htmlsecure($rec["nom"].' - '.date($config["format"]["date"].' '.$config["format"]["maniftime"],strtotime($rec["date"])).' - '.$rec["site"].' ('.$rec["ville"].')').
					'</option>';
			}
		?></select>
	</p>
	<p class="submit"><input type="submit" name="submit" value="ok" /></p>
</form>
</div>
<?php
	$request->free();
	$bd->free();
	includeLib("footer");
?>
