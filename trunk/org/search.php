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
	$default["supcreation"]		= $default["infcreation"] = "créa.";
	$default["supmodification"]	= $default["infmodification"] = "mod.";
	$default["groupname"]		= "-nom du groupe-";
	
	includeLib("headers");
	$print_search_fields = true;
		
	if ( $_POST )
	{
		// récupération des types de la table personne (pour la recherche sur les dates)
		$query	= " SELECT *
			    FROM organisme
			    LIMIT 1";
		$request = new bdRequest($bd,$query);
		$types = $request->getFields();
		$request->free();
		
		// formattage du tableau de question
		$fields = array();
		$req = array();
		for ( $i = 0 ; $_POST["field"]["categorie"][$i] || $_POST["field"]["cp"][$i] || $_POST["field"]["ville"][$i] ; $i++ )
		{
			$fields[] = array();
			$req[] = array();
			foreach ( $_POST["field"] as $key => $value )
			if ( $value[$i] != $_POST["organisme_".$key] &&
			     !( $_POST["organisme_creation"] == $value[$i] && substr($key,3) == "creation" ) &&
			     !( $_POST["organisme_modification"] == $value[$i] && substr($key,3) == "modification" ) )
			{
				$fields[count($fields)-1][$key] = $value[$i];
				if ( $key == "cp" )
					$req[count($req)-1][] = '"'.pg_escape_string($key).'" LIKE '."'".pg_escape_string($value[$i])."%'";
				else	$req[count($req)-1][] = '"'.pg_escape_string($key).'" = '."'".pg_escape_string($value[$i])."'";
			}
		}
		
		// formattage de la requete SQL
		for ( $i = 0 ; $i < count($req) ; $i++ )
			$req[$i] = implode(" AND ",$req[$i]);
		$req = implode(" OR ",$req);
		
		// requete !!
		$query	= " SELECT *
			    FROM organisme_categorie
			    WHERE ".$req;
		$organismes = new bdRequest($bd,$query);
		
		if ( $organismes->countRecords() > 0 )
			$print_search_fields = false;
	} // if ( $_POST ) 
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><?php printActions("org"); ?></p>
<div class="body">
<h2><?php echo $grpname ? "Groupe&nbsp;: ".htmlsecure($grpname) : "Rechercher ..." ?></h2>
<?php
	// groupes & extraction
	if ( $fields )
	{
?>
<div class="results">
	<h3>Le résultat de la recherche</h3>
	<ul><?php
		$orgs = array();
		$nb = 0;
		while ( $organismes && $rec = $organismes->getRecordNext() )
		if ( $rec["id"] != NULL )
		{
			$nb++;
			echo '<li>';
			echo '<a href="org/fiche.php?id='.intval($rec["id"]).'&view">';
			$orgs[] = intval($rec["id"]);
			echo htmlsecure($rec["nom"]);
			echo '</a> ('.htmlsecure($rec["catdesc"]).' - '.htmlsecure($rec["ville"]).')';
			echo '</li>';
		}
	?></ul>
	<p class="nbresults"><?php echo $nb." résultat(s)" ?></p>
</div>
<?php
	}
	
	// critères de recherche
	if ( $print_search_fields )
	{
?>
<form name="formu" class="search" action="<?php echo htmlsecure($_SERVER["PHP_SELF"])?>" method="post">
	<?php if ( $fields ) echo '<hr /><h2>Compléter les critères de recherche</h2>'; ?>
	<input type="hidden" name="organisme_cp" value="<?php echo htmlsecure($default["cp"]) ?>" />
	<input type="hidden" name="organisme_ville" value="<?php echo htmlsecure($default["ville"]) ?>" />
	<input type="hidden" name="organisme_creation" value="<?php echo $default["supcreation"] ?>" />
	<input type="hidden" name="organisme_modification" value="<?php echo $default["supmodification"] ?>" />
	<input type="hidden" id="hiddenmodel" />
	<div id="searchcond" class="recherche">
	<p class="titre">Critère de recherche</p>
	<p class="nosuppl">
		<span class="org">
			<select name="field[categorie][]">
			<?php
				$query	= " SELECT *
					    FROM org_categorie
					    ORDER BY libelle";
				$request = new bdRequest($bd,$query);
				
				echo '<option value="">-les catégories d\'organismes-</option>';
				while ( $rec = $request->getRecordNext() )
				{
					echo '<option value="'.intval($rec["id"]).'" class="cat">';
					echo htmlsecure($rec["libelle"]);
					echo '</option>';
				}
				
				$request->free();
			?>
			</select>
		</span>
		<span class="inter">ET</span>
		<span class="cp">Code Postal: <input type="text" name="field[cp][]" value="<?php echo htmlsecure($default["cp"]) ?>" maxlength="10" size="6" class="exemple" onfocus="javascript: ttt_onfocus(this,'<?php echo htmlsecure($default["cp"]) ?>')"onblur="javascript: ttt_onblur(this,'<?php echo htmlsecure($default["cp"]) ?>')" /></span>
		<span class="inter">ET</span>
		<span class="ville">Ville: <input type="text" name="field[ville][]" value="<?php echo htmlsecure(htmlsecure($default["ville"])) ?>" maxlength="255" size="12" class="exemple" onfocus="javascript: ttt_onfocus(this,'<?php echo htmlsecure($default["ville"]) ?>')"onblur="javascript: ttt_onblur(this,'<?php echo htmlsecure($default["ville"]) ?>')" /></span>
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
	<p class="or" id="searchor">
		<span><input type="button" name="or" value="&nbsp;...&nbsp;" onclick="javascript: ttt_searchorgor(document.getElementById('searchcond'),this.parentNode.parentNode);" /></span>
		<span class="desc">Rajouter un critère de recherche (OU)</span>
	</p>
	<p id="searchend" class="submit">
		<span class="submit"><input type="submit" name="search" value="Rechercher" /></span>
	</p>
</form>
<?php
	}
	
	// extraction
	if ( $fields && count($orgs) > 0 )
	{
?>
<form method="post" action="<?php echo htmlsecure('org/extract.php') ?>" class="extractor">
	<fieldset class="hidden"><?php
		foreach ( $orgs as $id )
			echo '<input type="hidden" name="id[]" value="'.intval($id).'" />';
	?></fieldset>
	<hr/>
	<h2>Extraire...</h2>
	<div class="org">
		<p class="pro">
			<span class="titre">Données de l'organisme</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="fields[]" value="catdesc" onclick="javascript: ttt_spanCheckBox(this);"/>
				Catégorie
			</span>
			<span class="onclick">
				<input type="checkbox" checked="checked" name="fields[]" value="nom" disabled="disabled"/>
				<input type="hidden" name="fields[]" value="nom"/>
				Nom
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="fields[]" value="adresse" onclick="javascript: ttt_spanCheckBox(this);"/>
				Adresse
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="fields[]" value="cp" onclick="javascript: ttt_spanCheckBox(this);"/>
				Code Postal
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="fields[]" value="ville" onclick="javascript: ttt_spanCheckBox(this);"/>
				Ville
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="fields[]" value="pays" onclick="javascript: ttt_spanCheckBox(this);"/>
				Pays
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="fields[]" value="email" onclick="javascript: ttt_spanCheckBox(this);"/>
				e-mail
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="fields[]" value="url" onclick="javascript: ttt_spanCheckBox(this);"/>
				Site Internet
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="fields[]" value="description" onclick="javascript: ttt_spanCheckBox(this);"/>
				Description
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="fields[]" value="telnum" onclick="javascript: ttt_spanCheckBox(this);"/>
				Numéro de téléphone (le premier saisi)
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="fields[]" value="teltype" onclick="javascript: ttt_spanCheckBox(this);"/>
				Type de téléphone (le premier saisi)
			</span>
		</p>
		<p class="system">
			<span class="titre">Options de l'export</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="printfields" value="yes" checked="checked" onclick="javascript: ttt_spanCheckBox(this);"/>
				Afficher le nom des champs
			</span>
			<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="msexcel" value="yes" onclick="javascript: ttt_spanCheckBox(this);"/>
				Compatibilité MSExcel au détriment des normes
			</span>
		</p>
	</div>
	<p><input type="submit" name="submit" value="Extraire" /></p>
</form>
</div>
<?php
	} // if ( $fields )
	
	if ( is_object($organismes) )
		$organismes->free();
	$bd->free();
	
	includeLib("footer");
?>
