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
	includeClass("bd/array");
	includeClass("bdRequest");
	includeClass("bdRequest/group");
	includeClass("bdRequest/array");
	includeLib("ttt");
	includeLib("actions");
	includeJS("ttt");
	$class = $class." search";
	
	$bd	= new arrayBd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	$action = $actions["edit"];
	
	// valeurs par défaut (la clé du tableau doit etre la même que la clé du tableau passé en POST)
	$default["cp"]			= "-29920-";
	$default["ville"]		= "-Bolazec-";
	$default["childmin"]		= "-âge min-";
	$default["childmax"]		= "-âge max-";
	$default["supcreation"]		= $default["infcreation"] = "créa.";
	$default["supmodification"]	= $default["infmodification"] = "mod.";
	$default["groupname"]		= "-nom du groupe-";
	
	// récupération des types de la table personne (pour la recherche sur les dates)
	$query	= " SELECT *
		    FROM personne
		    LIMIT 1";
	$request = new bdRequest($bd,$query);
	$types = $request->getFields();
	$request->free();
	
	// MISE EN FORME DES DONNEES pour la génération de la requete finale
	$fields = array();
	$fields_pers = array();
	
	// cleanage des valeurs vides ou par défaut
	for ( $j = $i = 0 ; $i < count($_GET["field"]["fctid"]) ; $i++ )
	{
		foreach ( $_GET["field"] as $key => $value )
			if ( $value[$i] != $default[$key] && $value[$i] )
			{
				$fields[$j][$key] = $value[$i];
				if ( $key == "org" )
				{
					unset($fields[$j][$key]);
					if ( preg_match('/orgid-/',$value[$i]) )
						$fields[$j]["orgid"] = intval(preg_replace('/orgid-/','',$value[$i]));
					else	$fields[$j]["orgcat"] = intval(preg_replace('/orgcat-/','',$value[$i]));
				}
			}
		if (isset($fields[$j])) $j++;
	}
	
	// génération de la requete spéciale non-professionnels
	for ( $i = 0 ; $i < count($fields) ; $i++ )
	{
		$forpers = true;
		foreach ( $fields[$i] as $key => $value )
			if ( !preg_match('/^personne\./',$key) ) $forpers = false;
		if ( $forpers ) $fields_pers[count($fields_pers)] = $fields[$i];
	}
	
	// si ce n'est pas un nouveau groupe, prends son id comme référence, sinon, prend tous les paramètres
	$fields = intval($_GET["grpid"]) > 0
		? intval($_GET["grpid"])
		: $fields;
	$print_search_fields = true;
	if ( isset($_GET["grpid"]) || isset($_GET["field"]) )
	{
		$personnes = new groupBdRequest($bd,$fields,$user,array("org"=>$_POST["org"],"pers"=>$_POST["pers"]));
		// LES GROUPES
		$group = $_POST["group"];
		if ( $group["new"] && $group["name"] && $group["name"] != $default["groupname"] )
		{
			if ( isset($group["dynamic"]) )
			{
				if ( $user->hasRight($config["right"]["group"]) )
				{
					if ( $personnes->writeSearch($group["name"], $group["dynamic"] == "yes", $group["all"] == "yes" ) )
					{
						// contournement d'un bug de sur-création de groupes...
						$user->addAlert("Votre groupe a bien été créé/modifié avec ".($personnes->countRecords()-1)." d'enregistrements");
						$nav->redirect('groups.php','Groupe créé avec '.($personnes->countRecords()-1)." d'enregistrements");
						
						// ancienne suite de l'algorithme
						$fields = $personnes->getGroupId();
						$personnes->free();
						$personnes = new groupBdRequest($bd,$fields,$user);
					}
					else	$user->addAlert("Erreur dans la création/modification de votre groupe");
					$grpname = $group["name"];
				}
				else	$user->addAlert("Vous n'avez pas de droits suffisants pour créer ou modifier des groupes");
			}
		}
		else	$grpname = $_GET["grpname"];
		
		$qstring = $_SERVER["QUERY_STRING"];
		$print_search_fields = false;
		//$print_search_fields = ($group["dynamic"] == "yes" || $personnes->getCondition() != array()) || (!isset($group["submit"]) && !$grpname) ? true : false;
		//$print_search_fields = $group["dynamic"] == "yes" || !isset($group["submit"] ? true : false;
	}
	else
	{
		$personnes = false;
		$fields = array();
	}
	
	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><?php printActions("ann"); ?></p>
<div class="body">
<h2><?php echo $grpname ? "Groupe&nbsp;: ".htmlsecure($grpname) : "Rechercher ..." ?></h2>
<?php
	// groupes & extraction
	if ( $fields )
	{
?>
<form class="results" action="<?php echo htmlsecure($_SERVER["PHP_SELF"]."?".$qstring) ?>" method="post">
	<h3>Le résultat de la recherche</h3>
	<div>
		<input type="checkbox" name="" value="" id="checkall" onchange="javascript: ttt_checkall(this.parentNode.parentNode,this);" />
		<span class="desc">inverser la sélection</span>
	</div>
	<ul><?php
		$csv = array();
		$nb = 0;
		$pointsAddr = array(); // geo-localisation
		while ( $personnes && $rec = $personnes->getRecordNext() )
		if ( $rec["id"] != NULL )
		{
			// geo-localisation
			if ( trim($rec["adresse"].$rec["cp"].$rec["ville"]) || trim($rec["orgadr"].$rec["orgcp"].$rec["orgville"]) )
			$pointsAddr[] = trim($rec["adresse"].$rec["cp"].$rec["ville"])
				? $rec["adresse"].", ".$rec["cp"]." ".$rec["ville"]
				: $rec["orgadr"].", ".$rec["orgcp"]." ".$rec["orgville"];
			
			$nb++;
			echo '<li class="'.($rec["npai"] == 't' ? "npai" : "").'">';
			echo '<span>';
			echo '<input type="checkbox" name="'.($rec["orgid"] ? "org[]" : "pers[]").'" value="'.($rec["orgid"] ? $rec["fctorgid"] : $rec["id"]).'" />';
			if ( $rec["orgid"] )
				$csv["fctorgid"][] = intval($rec["fctorgid"]);
			else	$csv["persid"][] = intval($rec["id"]);
			echo '</span> ';
			echo '<a href="ann/fiche.php?view&id='.$rec["id"].'">';
			echo htmlsecure($rec["nom"].' '.$rec["prenom"]);
			echo '</a>';
			if ( $rec["orgid"] != NULL )
				echo ' <span>(<a href="org/fiche.php?id='.$rec["orgid"].'&view">'.htmlsecure($rec["orgnom"]).'</a> - '.htmlsecure($rec["fctdesc"] ? $rec["fctdesc"] : $rec["fcttype"]).')</span>';
			echo '</li>';
		}
	?></ul>
	<p class="nbresults"><?php echo $nb." résultat(s)" ?></p>
	<p class="submit"><input type="submit" value="Retirer" name="rempers" /> les personnes sélectionnées</p>
	
	<?php
		//// géolocalisation ////
		if ( is_array($pointsAddr) && count($pointsAddr) && $config["gmap"]["enable"] )
		{
			includeLib("googlemap");
			echo '<h3>Géo-localisation</h3>';
			print_googlemap(NULL,$pointsAddr);
		}
	?>
	
	<?php
		$exclusions = $personnes->getExclusions();
		if ( count($exclusions["org"]) > 0 || count($exclusions["pers"]) > 0 )
		{
			echo '<h3>Les personnes exclues</h3>';
			echo '<ul>';
			
			$query	= "SELECT *
				   FROM personne_properso
				   WHERE ";
			$tmp = array();
			if ( count($exclusions["pers"]) > 0 ) $tmp[] = " (id IN (".implode(",",$exclusions["pers"]).") AND fctorgid IS NULL)";
			if ( count($exclusions["org"])  > 0 ) $tmp[] = " (fctorgid IN (".implode(",",$exclusions["org"]). "))";
			$query .= implode(" OR ",$tmp);
			$excl = new bdRequest($bd,$query);
			for ( $nb = 0 ; $rec = $excl->getRecordNext() ; $nb++ )
			{
				echo '<li class="'.($rec["npai"] == 't' ? "npai" : "").'">';
				echo '<span>';
				echo '<input type="checkbox" name="'.($rec["orgid"] ? "org[]" : "pers[]").'" value="'.($rec["orgid"] ? $rec["fctorgid"] : $rec["id"]).'" checked="checked"/>';
				/*
				if ( $rec["orgid"] )
					$csv["fctorgid"][] = $rec["fctorgid"];
				else	$csv["persid"][] = $rec["id"];
				*/
				echo '</span> ';
				echo '<a href="ann/fiche.php?view&id='.$rec["id"].'">';
				echo htmlsecure($rec["nom"].' '.$rec["prenom"]);
				echo '</a>';
				if ( $rec["orgid"] != NULL )
					echo ' <span>(<a href="org/fiche.php?id='.$rec["orgid"].'&view">'.htmlsecure($rec["orgnom"]).'</a> - '.htmlsecure($rec["fctdesc"] ? $rec["fctdesc"] : $rec["fcttype"]).')</span>';
				echo '</li>';
			}
			$excl->free();
			
			echo '</ul>';
			echo '<p class="nbresults">'.$nb.' exclusion(s)</p>';
			echo '<p class="submit"><input type="submit" value="Réintégrer" name="intpers" /> les personnes décochées</p>';
		}
	?>
	
	<h3>En faire un groupe...</h3>
	<p class="group">
		<span class="new onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
			<input type="checkbox" onclick="javascript: ttt_spanCheckBox(this);" name="group[new]" value="yes" />
			modifier ou créer le groupe
		</span>
		<span class="static onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
			<input	onclick="javascript: ttt_spanCheckBox(this);" type="radio" name="group[dynamic]" value="no"
				<?php echo $dynamic = $personnes->getCondition() == array() ? 'checked="checked"' : '' ?> />
			groupe statique
		</span>
		<span class="dynamic onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
			<input	onclick="javascript: ttt_spanCheckBox(this);" type="radio" name="group[dynamic]" value="yes"
				<?php echo $personnes->getCondition() != array() ? 'checked="checked"' : ''; ?> />
			groupe dynamique
		</span>
		<span class="onclick alluser" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
			<input onclick="javascript: ttt_spanCheckBox(this);" type="checkbox" name="group[all]" value="yes" />
			groupe disponible pour tous
		</span>
		<span class="name">
			nom du groupe&nbsp;:
			<input	type="text" name="group[name]" value="<?php echo htmlsecure($grpname ? $grpname : $default["groupname"]); ?>"
				class="<?php echo $grpname ? '' : 'exemple' ?>"
				onfocus="javascript: ttt_onfocus(this,'<?php echo $default["groupname"] ?>')"
				onblur="javascript: ttt_onblur(this,'<?php echo $default["groupname"] ?>')" />
		</span>
	</p>
	<p class="submit">
		<input type="submit" value="Valider" name="submit" />
		<span class="warning">Attention aux requêtes récursives à travers les groupes !!</span>
	</p>
</form>
<?php
	}
	
	if ( $print_search_fields )
	{
?>
<form name="formu" class="search" action="ann/search.php" method="get">
	<?php if ( $fields ) echo '<hr /><h2>Compléter les critères de recherche</h2>'; ?>
	<input type="hidden" name="personne_cp" value="<?php echo htmlsecure($default["cp"]) ?>" />
	<input type="hidden" name="personne_ville" value="<?php echo htmlsecure($default["ville"]) ?>" />
	<input type="hidden" name="childmax" value="<?php echo htmlsecure($default["childmax"]) ?>" />
	<input type="hidden" name="childmin" value="<?php echo htmlsecure($default["childmin"]) ?>" />
	<input type="hidden" name="personne_creation" value="<?php echo $default["supcreation"] ?>" />
	<input type="hidden" name="personne_modification" value="<?php echo $default["supmodification"] ?>" />
	<input type="hidden" id="hiddenmodel" />
	<div id="searchcond" class="recherche">
	<p class="titre">Critère de recherche</p>
	<p class="nosuppl">
		<span class="fctid">
			<select name="field[fctid][]">
			<?php
				$query	= " SELECT *
					    FROM fonction
					    ORDER BY libelle";
				$request = new bdRequest($bd,$query);
				
				echo '<option value="">-les fonctions-</option>';
				while ( $rec = $request->getRecordNext() )
					echo '<option value="'.intval($rec["id"]).'">'.htmlsecure($rec["libelle"]).'</option>';
				
				$request->free();
			?>
			</select>
		</span>
		<span class="inter">ET</span>
		<span class="org">
			<select name="field[org][]">
			<?php
				$query	= " SELECT *
					    FROM organisme_categorie
					    ORDER BY categorie,nom";
				$request = new bdRequest($bd,$query);
				
				echo '<option value="">-les organismes-</option>';
				$lastcat = 0;
				while ( $rec = $request->getRecordNext() )
				{
					if ( $lastcat != intval($rec["categorie"]) )
					{
						echo '<option value="orgcat-'.intval($rec["categorie"]).'" class="cat">';
						echo htmlsecure(is_null($rec["catdesc"]) ? "Sans catégorie" : $rec["catdesc"]);
						echo '</option>';
					}
					echo '<option value="orgid-'.$rec["id"].'" class="elt">';
					echo htmlsecure($rec["nom"].' ('.$rec["ville"].')');
					echo '</option>';
					$lastcat = intval($rec["categorie"]);
				}
				
				$request->free();
			?>
			</select>
		</span>
		<span class="inter">ET</span>
		<span class="group" name="spangroup">
			<select name="field[grpinc][0][]" multiple="multiple" size="3">
			<?php
				$query	= " SELECT (SELECT name FROM account WHERE id = groupe.createur) AS createur, createur AS createurid,
					           groupe.id, groupe.nom, (createur = ".$user->getId()." OR createur IS NULL) AS perso
					    FROM groupe
					    WHERE groupe.id NOT IN (SELECT groupid FROM groupe_andreq)
					    ORDER BY perso DESC, createur, nom";
				$request = new bdRequest($bd,$query);
				
				$lastcat = -1;
				while ( $rec = $request->getRecordNext() )
				{
					if ( $lastcat != intval($rec["createurid"]) )
					{
						if ( $lastcat != -1 ) echo '</optgroup>';
						if ( $rec["createurid"] == $user->getId() )
							echo '<optgroup label="Vos groupes">';
						elseif ( $rec["createurid"] != 0 )
							echo '<optgroup label="'.htmlsecure($rec["createur"]).'">';
						else	echo '<optgroup label="'.htmlsecure($default["commongrp"]).'">';
						$lastcat = intval($rec["createurid"]);
					}
					echo '<option value="'.intval($rec["id"]).'">';
					echo htmlsecure($rec["nom"]);
					echo '</option>';
				}
				echo '</optgroup>';
				
				$request->free();
			?>
			</select>
		</span>
		<span class="inter">ET</span>
		<span class="evenement"><select name="field[evenement][]" disabled="disabled"><option value="">-les évènements-</option></select></span>
		<span class="inter">ET</span>
		<span class="child">Enfants: <input type="text" name="field[childmin][]" value="<?php echo htmlsecure(htmlsecure($default["childmin"])) ?>" maxlength="255" size="12" class="exemple" onfocus="javascript: ttt_onfocus(this,'<?php echo htmlsecure($default["childmin"]) ?>')" onblur="javascript: ttt_onblur(this,'<?php echo htmlsecure($default["childmin"]) ?>')" /> - <input type="text" name="field[childmax][]" value="<?php echo htmlsecure(htmlsecure($default["childmax"])) ?>" maxlength="255" size="12" class="exemple" onfocus="javascript: ttt_onfocus(this,'<?php echo htmlsecure($default["childmax"]) ?>')" onblur="javascript: ttt_onblur(this,'<?php echo htmlsecure($default["childmax"]) ?>')" /></span>
		<span class="inter">ET</span>
		<span class="cp">Code Postal: <input type="text" name="field[cp][]" value="<?php echo htmlsecure($default["cp"]) ?>" maxlength="10" size="6" class="exemple" onfocus="javascript: ttt_onfocus(this,'<?php echo htmlsecure($default["cp"]) ?>')"onblur="javascript: ttt_onblur(this,'<?php echo htmlsecure($default["cp"]) ?>')" /></span>
		<span class="inter">ET</span>
		<span class="ville">Ville: <input type="text" name="field[ville][]" value="<?php echo htmlsecure(htmlsecure($default["ville"])) ?>" maxlength="255" size="12" class="exemple" onfocus="javascript: ttt_onfocus(this,'<?php echo htmlsecure($default["ville"]) ?>')" onblur="javascript: ttt_onblur(this,'<?php echo htmlsecure($default["ville"]) ?>')" /></span>
		<span class="inter">ET</span>
		<span class="npai" name="npai">NPAI <span>(oui|N/A)</span>&nbsp;? <input type="radio" name="field[npai][0]" value="t" /><input type="radio" checked="checked" name="field[npai][0]" value="" /> (ne voir que les NPAI)</span>
		<span class="inter">ET</span>
		<span class="email" name="email">e-mail <span>(non|N/A)</span>&nbsp;? <input type="radio" name="field[email][0]" value="t" /><input type="radio" checked="checked" name="field[email][0]" value="" /> (ne voir que ceux qui n'ont pas d'e-mail)</span>
		<span class="inter">ET</span>
		<span class="adresse" name="adresse">adresse <span>(non|N/A)</span>&nbsp;? <input type="radio" name="field[adresse][0]" value="t" /><input type="radio" checked="checked" name="field[adresse][0]" value="" />(ne voir que ceux qui n'ont pas d'adresse)</span>
		<span class="inter">ET</span>
		<span class="dates sup">
			Date &gt;=
			<input type="text" name="field[supcreation][]"		value="<?php echo $default["supcreation"] ?>" size="15" maxlength="127" class="exemple" onfocus="javascript: ttt_onfocus(this,'<?php echo $default["supcreation"] ?>')" onblur="javascript: ttt_onblur(this,'<?php echo $default["supcreation"] ?>')" />
			<input type="text" name="field[supmodification][]"	value="<?php echo $default["supmodification"] ?>" size="15" maxlength="127" class="exemple" onfocus="javascript: ttt_onfocus(this,'<?php echo $default["supmodification"] ?>')" onblur="javascript: ttt_onblur(this,'<?php echo $default["supmodification"] ?>')" />
		</span>
		<span class="inter">ET</span>
		<span class="dates inf">
			Date &lt;&nbsp;
			<input type="text" name="field[infcreation][]"		value="<?php echo $default["infcreation"] ?>" maxlength="127" size="15" class="exemple" onfocus="javascript: ttt_onfocus(this,'<?php echo $default["infcreation"] ?>')"onblur="javascript: ttt_onblur(this,'<?php echo $default["infcreation"] ?>')" />
			<input type="text" name="field[infmodification][]"	value="<?php echo $default["infmodification"] ?>" maxlength="127" size="15" class="exemple" onfocus="javascript: ttt_onfocus(this,'<?php echo $default["infmodification"] ?>')"onblur="javascript: ttt_onblur(this,'<?php echo $default["infmodification"] ?>')" />
		</span>
		<span class="hide"><a onclick="javascript: e=this.parentNode.parentNode; if(e.className=='')e.className='nosuppl';else e.className='';">options</a></span>
	</p>
	</div>
	<p class="or" id="searchor"><!--
		<span><input type="button" name="or" value="&nbsp;...&nbsp;" onclick="javascript: ttt_searchor(document.getElementById('searchcond'),this.parentNode.parentNode);" /></span>
		<span class="desc">Rajouter un critère de recherche (OU)</span>
	--></p>
	<p id="searchend" class="submit">
		<span class="submit"><input type="submit" name="search" value="Rechercher" /></span>
	</p>
</form>
<?php
	}
	
	if ( $fields )
	{
		$query	= " SELECT value
			    FROM options
			    WHERE accountid = ".$user->getId()."
			      AND key = 'ann.extractor'";
		$req = new bdRequest($bd,$query);
		$presel = split(';',$req->getRecord("value"));
		$req->free();
?>
<form method="post" action="<?php echo htmlsecure('ann/extract.php?'.$qstring) ?>" class="extractor">
	<fieldset class="hidden"><?php
		if ( $dynamic && is_int($fields) )
			echo '<input type="hidden" name="csv[group]" value="'.$fields.'" />';
		else
		{
			if ( count($csv["persid"]) > 0 )
			foreach ( $csv["persid"] as $value )
				echo '<input type="hidden" name="csv[persid][]" value="'.htmlsecure($value).'" />';
			
			if ( count($csv["fctorgid"]) > 0 )
			foreach ( $csv["fctorgid"] as $value )
				echo '<input type="hidden" name="csv[fctorgid][]" value="'.htmlsecure($value).'" />';
		}
	?></fieldset>
	<hr/>
	<h2>Extraire...</h2>
	<div>
		<p class="perso">
			<span class="titre">Données personnelles</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" <?php if ( in_array("titre",$presel) ) echo 'checked="checked"' ?> name="csv[fields][]" value="titre" onclick="javascript: ttt_spanCheckBox(this);"/>
				Titre
			</span>
			<span class="onclick">
				<input type="checkbox" checked="checked" name="csv[fields][]" value="nom" disabled="disabled"/>
				<input type="hidden" name="csv[fields][]" value="nom" />
				Nom
			</span>
			<span class="onclick">
				<input type="checkbox" checked="checked" name="csv[fields][]" value="prenom" disabled="disabled"/>
				<input type="hidden" name="csv[fields][]" value="prenom" />
				Prénom
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="adresse" <?php if ( in_array("adresse",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Adresse
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="cp" <?php if ( in_array("cp",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Code Postal
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="ville" <?php if ( in_array("ville",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Ville
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="pays" <?php if ( in_array("pays",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Pays
			</span>
			<span class="onclick npai" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="npai" <?php if ( in_array("npai",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				NPAI
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" <?php if ( in_array("email",$presel) ) echo 'checked="checked"' ?> value="email" onclick="javascript: ttt_spanCheckBox(this);"/>
				e-mail
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" <?php if ( in_array("telnum",$presel) ) echo 'checked="checked"' ?> value="telnum" onclick="javascript: ttt_spanCheckBox(this);"/>
				Numéro de téléphone (le premier saisi)
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="teltype" <?php if ( in_array("teltype",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Type de téléphone (le premier saisi)
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="creation" <?php if ( in_array("creation",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Date de création
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="modification" <?php if ( in_array("modification",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Date de modification
			</span>
		</p>
		<p class="properso">
			<span class="titre">Données liées à l'organisme</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="protel" <?php if ( in_array("protel",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Téléphone professionnel
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="proemail" <?php if ( in_array("proemail",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				e-mail professionnel
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="fcttype" <?php if ( in_array("fcttype",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Fonction type
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="fctdesc" <?php if ( in_array("fctdesc",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Fonction personnalisée
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="service" <?php if ( in_array("service",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Service
			</span>
		</p>
		<p class="pro">
			<span class="titre">Données de l'organisme</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgcat" <?php if ( in_array("orgcat",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Catégorie
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgnom" <?php if ( in_array("orgnom",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Nom
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgadr" <?php if ( in_array("orgadr",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Adresse
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgcp" <?php if ( in_array("orgcp",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Code Postal
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgville" <?php if ( in_array("orgville",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Ville
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgpays" <?php if ( in_array("orgpays",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Pays
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgemail" <?php if ( in_array("orgemail",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				e-mail
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgurl" <?php if ( in_array("orgurl",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Site Internet
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgdesc" <?php if ( in_array("orgdesc",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Description
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgtelnum" <?php if ( in_array("orgtelnum",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Numéro de téléphone (le premier saisi)
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="orgteltype" <?php if ( in_array("orgteltype",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Type de téléphone (le premier saisi)
			</span>
		</p>
		<p class="other">
			<span class="titre">Autres</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="csv[fields][]" value="info" <?php if ( in_array("info",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Informations complémentaires contextuelles
			</span>
		</p>
		<p class="system">
			<span class="titre">Options de l'export</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="printfields" value="yes" <?php if ( in_array("printfields",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Afficher le nom des champs
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="entonnoir" value="yes" <?php if ( in_array("entonnoir",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Préférer les données professionnelles
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="msexcel" value="yes" <?php if ( in_array("msexcel",$presel) ) echo 'checked="checked"' ?> onclick="javascript: ttt_spanCheckBox(this);"/>
				Compatibilité MSExcel au détriment des normes
			</span>
		</p>
	</div>
	<p><input type="submit" name="submit" value="Extraire" /></p>
</form>
<?php	} // if ( $fields ) ?>
</div>
<script language="javascript">
<?php
if ( $personnes )
{
	// les conditions à remplir graphiquement
	$fields = $personnes->getCondition();
	
	// fait le boulot de cleanage des input de type text
	function inputText($default,$fields,$i,$key)
	{
		$value = $fields[$i][$key] ? htmlsecure($fields[$i][$key]) : $default[$key];
		echo "inputs.item(i).value = '".$value."';";
		if ( $fields[$i][$key] && $fields[$i][$key] != $default[$key] )
			echo 'inputs.item(i++).setAttribute("class","");';
		else	echo 'i++;';
	}		
	for ( $i = 0 ; $i < count($fields) ; $i++ )
	{
?>
		para = document.getElementById("searchcond");
		// param inputs
		inputs = para.getElementsByTagName("input");
		i = 0;
<?php
		// text
		foreach ( array("childmin","childmax","cp","ville") as $key )
			inputText($default,$personnes->getCondition(),$i,$key);
		// checkboxes
		foreach ( array("npai","email","adresse") as $key )
		{
			if ( $fields[$i][$key] == 't' )
			{
				echo "inputs.item(i++).checked = true;";
				echo "inputs.item(i++).checked = false;";
			}
			else	echo 'i=i+2;';
		}

		// text
		foreach ( array("supcreation","supmodification",
				"infcreation","infmodification")
				 as $key )
		{
			inputText($default,$fields,$i,$key);
		}
		// param selects
?>
		selects = para.getElementsByTagName("select");
		i = 0;
<?php
		foreach ( array("fctid","orgid","grpinc","evenement") as $key )
		{
			$selectedValues = !is_array($fields[$i][$key]) ? array($fields[$i][$key]) : $fields[$i][$key];
			if ( $key == "orgid" )
			{
				if ( $fields[$i][$key] )
					$selectedValues	= array("orgid-".intval($fields[$i][$key]));
				elseif ( intval($fields[$i]["orgcat"]) > 0 )
					$selectedValues = array("orgcat-".intval($fields[$i]["orgcat"]));
				else	$selectedValues = array(0);
			}
			$cond = 'selects.item(i).options.item(j).getAttribute("value")+""';
?>
			for ( j = 0 ; j < selects.item(i).options.length ; j++ )
			{
				if ( <?php echo $cond ?> == "<?php echo implode('" || '.$cond.' == "',$selectedValues) ?>" )
					selects.item(i).options.item(j).selected = true;
			}
			i++;
<?php		} ?>
		// clonage
		//ttt_searchor(para,document.getElementById("searchor"));
<?php	} // for ( $i = 0 ; $i < count($fields["nom"]) ; $i++ ) ?>
<?php
	$request->free();
	$personnes->free();
}
?>
</script>
<?php
	$bd->free();
	includeLib("footer");
?>
