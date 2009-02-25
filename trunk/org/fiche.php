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
	includeLib("ttt");
	includeJS("ttt");
	includeLib("actions");
	includeJS("jquery");
	includeJS("jquery.contact");
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	$id = intval($_GET["id"]);

	// les droits
	switch ( $action ) {
	case $actions["add"]:
		$user->redirectIfNoRight($nav,$config["right"]["add"]);
		$id = 0;
		break;
	case $actions["edit"]:
		$user->redirectIfNoRight($nav,$config["right"]["edit"]);
		break;
	case $actions["view"]:
		$user->redirectIfNoRight($nav,$config["right"]["view"]);
		break;
	}
	
	// L'entête
	includeLib("headers");
	echo '<h1>'.$title.'</h1>';
	includeLib("tree-view");
	
	// mise en forme des données
	$new = &$_POST["field"];
	$tel = &$_POST["tel"];
	
	if ( !isset($new["categorie"]["value"]) )
		$new["categorie"]["value"] = NULL;
	
	if ( isset($_POST["valid"])
	  && (( $orgpersonneid > 0 && $user->hasRight($config["right"]["edit"]) )||( $personneid <= 0 && $user->hasRight($config["right"]["add"]) ))
	  && $new["nom"]["value"]	!= "" && $new["nom"]["value"]		!= $new["nom"]["default"] )
	{
		// modification de l'organisme à proprement parlé
		$arr = array();
		foreach ( $new as $key => $value )
		{
			if ( $value["value"] != $value["default"] && $value["value"] != "" )
				$arr[pg_escape_string($key)] = $value["value"];
			else	$arr[pg_escape_string($key)] = NULL;
		}
		$arr["modification"] = date("Y-m-d H:i:s");
		
		if ( intval($_POST["id"]) > 0 && $id == $_POST["id"] )
			$ppl = $bd->updateRecordsSimple("organisme",array("id" => intval($_POST["id"])),$arr);
		elseif ( $id == 0 )
		{
			$ppl = $bd->addRecord("organisme",$arr);
			$id = intval($bd->getLastSerial("entite","id"));
			$action = $actions["view"];
		}
		
		// gestion des numéro de téléphone
		if ( $id > 0 )
		{
			// nettoyage des vieux téléphones
			$typedef = $_POST["typedefault"];
			$numdef  = $_POST["numdefault"];
			
			$bd->delRecordsSimple("telephone_organisme",array("entiteid" => $id));
			for ( $i = count($tel["num"]) -1 ; isset($tel["num"][$i]["value"]) ; $i-- )
			{
				if ( $tel["num"][$i]["value"] != $numdef && $tel["num"][$i]["value"] != "" )
				{
					$arr = array();
					if ( ($tel["type"][$i]["value"] != $typedef && $tel["type"][$i]["value"] != "") )
						$arr["type"] = pg_escape_string($tel["type"][$i]["value"]);
					$arr["numero"] = pg_escape_string($tel["num"][$i]["value"]);
					$arr["entiteid"] = $id;
					$bd->addRecord("telephone_organisme",$arr);
				}
			} // for ( $i = count($tel["num"]) -1 ; isset($tel["num"][$i]["value"]) ; $i-- )
		} // if ( $id > 0 )
	}
	elseif ( isset($_POST["valid"]) )
		$user->addAlert("Problème lors de la modification ou la création de votre fiche<br/>");
	
	// Les valeurs "modèles"
	$typeDefault = "-type-";
	$numDefault = "-05 87 95 35 32-";
	
	// acquisition des données à afficher
	$request = false;
	if ( $id > 0 && $action != $actions["add"] )
	{
		$query	= " SELECT *
			    FROM organisme_categorie
			    WHERE id = ".$id;
		$request = new bdRequest($bd,$query);
			
		if ( $request->countRecords() == 0 )
		{
			$request->free();
			$request = false;
		}
	}
	elseif ( $action != $actions["add"] )
	{
		$user->addAlert("L'organisme recherché n'existe pas");
		$action = $actions["add"];
	}
?>
<p class="actions"><?php printActions("org"); ?></p>
<div class="body">
<?php
	
	if ( $request )
	{
		$rec = $request->getRecord();
		$request->free();
	}
?>
<form class="organisme" name="formu" action="org/fiche.php?id=<?php echo $id ?>" method="post">
	<div class="organisme">
		<p class="titre">Organisme</p>
		<input type="hidden" name="typedefault" value="<?php echo htmlsecure($typeDefault) ?>" />
		<input type="hidden" name="numdefault" value="<?php echo htmlsecure($numDefault) ?>" />
		<input type="hidden" name="id" value="<?php echo $id ?>" />
		<p class="global">
			<span><?php printField("field[".($name = "nom")."]",$rec[$name],"-La Grande Cie-",127,NULL,NULL,NULL,NULL,NULL,'id="focus"') ?></span>
		</p>
		<p class="adresse">
			<?php $address = trim($rec["adresse"].$rec["cp"].$rec["ville"].$rec["pays"]) ? $rec["adresse"].", ".$rec["cp"]." ".$rec["ville"].", ".$rec["pays"] : ""; ?>
			<span><?php printField("field[".($name = "adresse")."]",$rec[$name],"-3, Hent Ty Menhir-",NULL,NULL,true) ?></span>
			<br/>
			<span><?php printField("field[".($name = "cp")."]",$rec[$name],"-29640-",10,6) ?></span>
			<span><?php printField("field[".($name = "ville")."]",$rec[$name],"-Bolazec-",255) ?></span>
			<br />
			<span><?php printField("field[".($name = "pays")."]",$rec[$name],"-France-",255) ?></span>
		</p>
		<p class="email">
			<span>
			<?php
				if ( $action == $actions["view"] ) echo '<a class="email" href="mailto:'.$rec["email"].'">';
				printField("field[".($name = "email")."]",$rec[$name],"-lagrandecie@dom.tld-",255);
				if ( $action == $actions["view"] ) echo '</a>';
			?>
			</span>
		</p>
		<p class="url">
			<span>
			<?php
				if ( $action == $actions["view"] ) echo '<a class="url" href="'.$rec["url"].'">';
				printField("field[".($name = "url")."]",$rec[$name],"-http://www.leursiteweb.com/-",255,30);
				if ( $action == $actions["view"] ) echo '</a>';
			?>
			</span>
		</p>
		<p class="description">
			<span><?php printField("field[".($name = "description")."]",$rec[$name],$default[$name],NULL,NULL,true); ?></span>
		</p>
		<p class="categorie">
				<span>
			<?php
				if ( $action != $actions["view"] )
				{
					$query	= " SELECT * FROM org_categorie ORDER BY libelle";
					$cats = new bdRequest($bd,$query);
					echo '<select name="field[categorie][value]">	<option value="">-sans catégorie-</option>';
					while ( $cat = $cats->getRecordNext() )
					{
						echo '<option value="'.intval($cat["id"]).'"';
						if ( intval($rec["categorie"]) == intval($cat["id"]) )
							echo 'selected="selected"';
						echo '>'.htmlsecure($cat[libelle]).'</option>';
					}
					echo '</select>';
					$cats->free();
				}
				elseif ( $rec["catdesc"] ) echo htmlsecure("Catégorie: ".$rec["catdesc"]);
			?>
			</span>
		</p>
		<?php if ( $action != $actions["add"] && $rec["creation"] && $rec["modification"] ) { ?>
		<p class="dates">
			<span>Créé le <?php echo date($config["format"]["date"].' à '.$config["format"]["time"],strtotime($rec["creation"])) ?></span>
			<span>Modifié le <?php echo date($config["format"]["date"].' à '.$config["format"]["time"],strtotime($rec["modification"])) ?></span>
		</p>
		<?php } ?>
	</div>
	<div class="tel jqslide">
		<p class="titre">Téléphones de l'organisme</p>
		<div>
		<?php
			if ( $action == $actions["edit"] || $action == $actions["add"] )
			{
			
				$query	= " SELECT str AS type FROM str_model WHERE usage = 'teltype' ORDER BY str";
				$typegen = new bdRequest($bd,$query);
		?>
		<p class="tel" id="telmodel">
			<span class="type">
				<input	type="text" name="tel[type][][value]"
					class="exemple"
					value="<?php echo $typeDefault ?>"
					onfocus="javascript:ttt_onfocus(this,'<?php echo $typeDefault ?>');"
					onblur="javascript: ttt_onblur(this,'<?php echo $typeDefault ?>');"
					size="16" maxlength="127" />
				<select	name="tel[typegen][][value]"
					size="<?php echo $typegen->countRecords() + 1 ?>"
					onchange="javascript: ttt_teltypegen(this,'<?php echo $typeDefault ?>');">
					<option value=""></option>
					<?php
						$typegen->firstRecord();
						while ( $rec = $typegen->getRecordNext("type") )
							echo '<option value="'.$rec.'">'.$rec.'</option>';
					?>
				</select>
			</span>
			<span class="num">
				<input	type="text" name="tel[num][][value]"
					class="exemple"
					value="<?php echo $numDefault ?>" size="14" maxlength="40"
					onfocus="javascript: ttt_onfocus(this,'<?php echo $numDefault ?>');"
					onblur="javascript: ttt_tel(this,'<?php echo $numDefault ?>',true);" />
			</span>
		</p>
		<?php
			} // if ( $action == $actions["edit"] || $action == $actions["add"])
			
			$query	= " SELECT *
				    FROM telephone_organisme
				    WHERE entiteid = ".$id."
				    ORDER BY id DESC";
			if ( $action == $actions["add"] ) $query = NULL;
			$request = new bdRequest($bd,$query);
			
			while ( $rec = $request->getRecordNext() )
			{
		?>
		<p class="tel">
			<span class="type">
				<?php
					printField("tel[type][]",$rec["type"],$typeDefault,127,16,false,NULL,NULL,false);
					if ( $action == $actions["edit"] || $action == $actions["add"] )
					{
				?>
				<select	name="tel[typegen][][value]"
					size="<?php echo $typegen->countRecords() + 1 ?>"
					onchange="javascript: ttt_teltypegen(this,'<?php echo $typeDefault ?>');">
					<option value=""></option>
					<?php
						$typegen->firstRecord();
						while ( $type = $typegen->getRecordNext("type") )
							echo '<option value="'.$type.'">'.$type.'</option>';
					?>
				</select>
				<?php } ?>
			</span>
			<span class="num"><?php printField("tel[num][]",$rec["numero"],$numDefault,40,14,false,NULL,"ttt_tel(this,'".$numDefault."',false)",false); ?></span>
		</p>
		<?php
			} // while ( $rec = $request->getRecordNext() )
			$request->free();
			if ( is_object($typegen) ) $typegen->free();
		?>
		</div>
	</div>
	<?php
		if ( $action == $actions["edit"] || $action == $actions["add"] )
			echo '<p class="valid"><input type="submit" name="valid" value="valider" /></p>';
	?>
</form>
<?php
	// googlemap
	if ( $action == $actions["view"] && $config["gmap"]["enable"] )
	{
		includeLib("googlemap");
		print_googlemap($address);
	}
	
	if ( $id > 0 )
	{
?>
<div class="contacts jqslide">
	<p class="titre">Membres de l'organisme</p>
	<div class="clip">
	<?php
		// Affichage des personnes liées à l'organisme
		if ( $id > 0 )
		{
			$query	= " SELECT *
				    FROM personne_properso
				    WHERE orgid = ".$id;
			if ( $action == $actions["add"] ) $query = NULL;
			$request = new bdRequest($bd,$query);
			
			while ( $rec = $request->getRecordNext() )
			{
				echo '<p>';
				echo '<span class="nom"><a href="ann/fiche.php?id='.$rec["id"].'&view">'.$rec["nom"]." ".$rec["prenom"]."</a></span>\n";
				echo '<br />';
				echo '<span class="fonction">'.htmlsecure($rec["fctdesc"].'('.$rec["fcttype"].')')."</span>\n";
				echo "</span>\n";
					echo '<br />';
				echo '<span class="service">'.$rec["service"]."</span>\n";
				echo '</p>';
			}
			$request->free();
		}
	?>
	</div>
</div>
<?php
	}
	@includePage("../evt/infos/organisme",false);
?>
</div>
<?php
	$bd->free();
	includeLib("footer");
?>
