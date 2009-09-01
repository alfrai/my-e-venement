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
	includeLib("actions");
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	includeLib("headers");
	
	$candelcommongrp = $user->hasRight($config["right"]["commongrp"]);
	
	if ( isset($_POST["submit"]) )
	if ( $user->hasRight($config["right"]["group"]) )
	{
		// suppression des groupes
		if ( is_array($del = $_POST["del"]) && count($del) > 0 && $_POST["nodel"] != "yes" )
		{
			foreach ( $del as $key => $value )
				$del[$key] = intval($value);
			$todel = implode(' OR id = ',$del);
			$bd->delRecords("groupe","(createur = ".$user->getId()." OR createur IS NULL) AND (id = ".$todel.")");
			$user->addAlert("Vous avez supprimé ".count($del)." groupes.");
		}
		
		// renommage des groupes
		if ( is_array($namen = $_POST["namen"]) && count($namen) > 0
		  && is_array($nameo = $_POST["nameo"]) && count($nameo) > 0
		  && count($namen) == count($nameo) )
		{
			$mod = $err = 0;
			foreach ( $namen as $key => $value )
			{
				if ( intval($key)."" == $key."" && $nameo[$key] != $namen[$key] )
				{
					if ( $bd->updateRecordsSimple("groupe",array("id"=>intval($key)),array("nom"=>$value)) )
						$mod++;
					else	$err++;
				}
			}
			if ( $err > 0 || $mod > 0 ) $user->addAlert("Vous avez modifié ".$mod." groupe(s) pour ".$err." erreur(s)");
		}
	}
	else	$user->addAlert("Vous n'avez pas les droits nécessaires pour modifier ou supprimer des groupes");
	
	// créer un groupe vide
	if ( isset($_GET["creer"]) && $_GET["common"] && $_GET["nom"] )
	{
		$arr = array();
		$arr["nom"] = $_GET["nom"];
		$arr["createur"] = $_GET["common"] == "yes" ? NULL : $user->getId();
		if ( $bd->addRecord("groupe",$arr) )
			$user->addAlert("Votre groupe a bien été créé.");
		else	$user->addAlert("Erreur lors de la création de votre groupe.");
	}
	
	$groupid = intval($_GET["id"]);
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require('actions.php'); ?>
<div class="body">
<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="get" class="mygroups">
	<h2>Créer un groupe statique vide</h2>
	<p>Nom: <input type="text" name="nom" value="" /></p>
	<?php
		if ( $candelcommongrp )
		{
	?>
	<p>
		Commun ?
		<span><input type="radio" name="common" value="yes" />oui</span>
		<span><input type="radio" name="common" value="no" checked="checked" />non</span>
	</p>
	<?php	} else { ?>
		<input type="hidden" name="common" value="no" />
	<?php	} ?>
	<p><input type="submit" name="creer" value="Créer" />
</form>
<form name="formu" action="<?php echo $_SERVER["PHP_SELF"] ?>" method="post" class="mygroups">
<h2>Tous mes groupes</h2>
<p class="desc">suppr.</p>
<?php
	$query	= " SELECT *
		    FROM groupe
		    WHERE createur = ".$user->getId()."
		    ORDER BY nom";
	$request = new bdRequest($bd,$query);
	while ( $rec = $request->getRecordNext() )
	{
		echo '<p>';
		echo '<input onclick="javascript: document.formu.sttdel.value = \'y\';" type="checkbox" name="del[]" value="'.intval($rec["id"]).'" /> ';
		echo '<input onclick="javascript: document.formu.sttdel.value = \'y\';" type="hidden" name="nameo['.intval($rec["id"]).']" value="'.htmlsecure($rec["nom"]).'" /> ';
		echo '<input onclick="javascript: document.formu.sttdel.value = \'y\';" type="text" name="namen['.intval($rec["id"]).']" value="'.htmlsecure($rec["nom"]).'" maxlength="255" /> ';
		echo '<a href="ann/search.php?grpid='.intval($rec["id"]).'&grpname='.urlencode($rec["nom"]).'" class="voir">voir...</a> '.($rec["description"] ? '<span class="desc">'.htmlsecure($rec["description"]).'</span>' : '' );
		echo '<a href="ann/emailing.php?grpid='.intval($rec["id"]).'&grpname='.urlencode($rec["nom"]).'">courieliser...</a>';
		echo '</p>';
	}
?>
<h2>Tous les groupes communs</h2>
<?php if ( $candelcommongrp ) { ?><p class="desc">suppr.</p><?php } ?>
<input type="hidden" name="nodel" value="" />
<input type="hidden" name="sttdel" value="n" />
<?php
	$query	= " SELECT *
		    FROM groupe
		    WHERE createur IS NULL
		    ORDER BY nom";
	$request = new bdRequest($bd,$query);
	while ( $rec = $request->getRecordNext() )
	{
		echo '<p>';
		if ( $candelcommongrp )
		echo '<input type="checkbox" onclick="javascript: document.formu.sttdel.value = \'y\';" name="del[]" value="'.intval($rec["id"]).'" /> ';
		echo '<input type="hidden" name="nameo['.intval($rec["id"]).']" value="'.htmlsecure($rec["nom"]).'" /> ';
		echo '<input type="text" name="namen['.intval($rec["id"]).']" value="'.htmlsecure($rec["nom"]).'" maxlength="255" /> ';
		echo '<a href="ann/search.php?grpid='.intval($rec["id"]).'&grpname='.urlencode($rec["nom"]).'">voir...</a> ';
		echo '<a href="ann/emailing.php?grpid='.intval($rec["id"]).'&grpname='.urlencode($rec["nom"]).'">courieliser...</a>';
		echo '</p>';
	}
?>
<p><input type="submit" name="submit" value="Valider"
	  onclick="javascript: if ( document.formu.sttdel.value != 'n' ) if ( !confirm('Êtes vous bien sûr(e) de vouloir\nsupprimer les groupes sélectionnés ?') ) document.formu.nodel.value = 'yes';
   "/> (!! les suppressions comme les modifications !!)</p>
</form>
<div class="allgroups">
<h2>Tous les groupes</h2>
<p>
	<span><a href="javascript: document.getElementById('allgroups').setAttribute('class','');">Montrer</a></span>
	<span><a href="javascript: document.getElementById('allgroups').setAttribute('class','nodisplay');">Cacher</a></span>
</p>
<ul id="allgroups" class="nodisplay">
<?php
	$query	= " SELECT groupe.*, account.name AS createur
		    FROM groupe, account
		    WHERE groupe.createur = account.id
		      AND groupe.createur != ".$user->getId()."
		    ORDER BY account.name, groupe.nom";
	$request = new bdRequest($bd,$query);
	while ( $rec = $request->getRecordNext() )
		echo '<li>'.htmlsecure($rec["createur"]).': <a href="ann/search.php?grpid='.intval($rec["id"]).'"&grpname='.htmlsecure($rec["nom"]).'>'.htmlsecure($rec["nom"]).'</a></li>';
?>
</ul>
</div>
</div>
<?php
	$bd->free();
	includeLib("footer");
?>
