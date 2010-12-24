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
	includeClass("bdRequest/array");
	includeLib("ttt");
	includeLib("actions");
	includeLib("jauge");
	includeJS("ajax");
	includeJS("ttt");
	includeJS("annu");
	
	if ( isset($_GET["del"]) )
		$action = $actions["del"];
	
	if ( !($mod = $user->evtlevel >= $config["evt"]["right"]["mod"]) )
	if ( $action != $actions["view"] )
	{
		$user->addAlert("Vous ne pouvez que consulter ces informations");
		$action = $actions["view"];
	}

	$salle = true;
	$class = "salle";
	$css[] = "evt/styles/colors.css.php";
	$css[] = "evt/styles/jauge.css";
	$id = intval($_GET["id"]);
	
	// Suppression
	if ( $action == $actions["del"] && $mod )
	if ( $id > 0 )
	{
		if ( $_GET["oui"] == "oui" && isset($_GET["deep"]) )
			$r = $bd->delRecordsSimple("site",array("id" => $id));
		elseif ( $_GET["oui"] == "oui" )
			$r = $bd->updateRecordsSimple("site",array("id" => $id),array("active" => 'f'));
		if ( $_GET["oui"] == "oui" )
		if ( $r )
		{
			$user->addAlert($msg = "Salle supprimée");
			$nav->redirect("./salles.php?s=".substr($_GET["name"],0,1),$msg);
		}
		else
		{
			$user->addAlert("Impossible de supprimer la salle.");
			$action = $actions["view"];
		}
	} // if ( $id > 0 )
	else	$action = $actions["view"];
	
	includeLib("headers");
	
	$fctregisseur = "Régisseur";
	$default["nom"] = "-la grande salle-";
	$default["dim"][] = "L";
	$default["dim"][] = "P";
	$default["dim"][] = "H";
	
	// Enregistrement d'une nouvelle salle / modification d'une salle existante
	$fields = $_POST["field"];
	if ( $fields["nom"]["value"] && $fields["nom"]["value"] != $fields["nom"]["default"] && $mod )
	{
		$rec = array();
		foreach ( array("adresse","cp","ville","pays","regisseur","organisme","description",
				"dimensions_salle","dimensions_scene","noir_possible","gradins",
				"amperage", "capacity", 'jauge_min', 'jauge_max')
				as $value )
			$rec[$value] = NULL;
		
		foreach ( $fields as $key => $value )
		{
			if ( $value["value"] && $value["value"] != $value["default"] )
				$rec[$key] = $value["value"];
		}
		
		// les tableaux
		for ( $i = 0 ; $i < 3 && intval($fields["dimensions_salle"][$i]["value"]) > 0 ; $i++ )
		{
			$rec["dimensions_salle"][$i] = intval($fields["dimensions_salle"][$i]["value"]);
			echo "tour ".$i;
		}
		for ( $i = 0 ; $i < 3 && intval($fields["dimensions_scene"][$i]["value"]) > 0 ; $i++ )
		{
			$rec["dimensions_scene"][$i] = intval($fields["dimensions_scene"][$i]["value"]);
			echo "glop";
		}
		$rec["modification"] = date("Y-m-d H:i:s");
		
		// les entiers
		foreach ( array("organisme","regisseur","capacity") as $value )
			if ( $rec[$value] ) $rec[$value] = intval($rec[$value]);
		
		// le plan dynamic
		if ( isset($_POST["delmap"]) )
			$rec["dynamicplan"] = NULL;
		// ajouter le contenu du fichier uploadé
		
		if ( $id <= 0 )
		{
			if ( $bd->addRecord("site",$rec) )
				$id = $bd->getLastSerial("site","id");
			else
			  $user->addAlert("Le site n'a pu être créé");
		}
		elseif ( !$bd->updateRecordsSimple("site",array('id' => $id),$rec) )
			$user->addAlert("Le site n'a pu être modifié");
	}
	
	// La consultation
	$rec = false;
	if ( $id > 0 && $action != $actions["add"] )
	{
		$query = " SELECT *
			   FROM site_datas AS site
			   WHERE active = 't'
			     AND site.id = ".$id;
		$request = new arrayBdRequest($bd,$query);
		
		if ( $request->countRecords() == 0 )
		{
			$request->free();
			$user->addAlert("La salle recherchée n'existe pas");
			$action = $actions["add"];
		}
		else	$rec = $request->getRecordNext();
	}
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
<?php
	if ( $action != $actions["del"] )
	{
?>
<form class="salle" name="formu" method="post" action="<?php echo htmlsecure($_SERVER["PHP_SELF"]).'?'.($action != $actions["add"] ? "id=".$id."&" : "")."view"; ?>">
	<?php // les valeurs par défaut
		foreach ( $default as $key => $value ) if ( !is_array($value) ) echo '<input type="hidden" name="def'.htmlsecure($key).'" value="'.htmlsecure($value).'" />';
	?>
	<h2>Salle</h2>
	<div class="infos">
		<p class="titre">Informations générales</p>
		<div class="clip">
			<p class="global"><?php
				printField("field[".($name = "nom")."]",$rec[$name],$default[$name],255,30);
			?></p>
			<p class="adresse">
				<?php printField("field[".($name = "adresse")."]",$rec[$name],$default[$name],NULL,NULL,true); ?><br/>
				<?php printField("field[".($name = "cp")."]",$rec[$name],$default[$name],12,10); ?>
				<?php printField("field[".($name = "ville")."]",$rec[$name],$default[$name],255); ?><br/>
				<?php printField("field[".($name = "pays")."]",$rec[$name],$default[$name],255); ?>
			</p>
			<p class="desc"><?php printField("field[".($name = "description")."]",$rec[$name],$default[$name],NULL,NULL,true); ?></p>
			<?php if ( $action != $actions["add"] ) { ?>
			<p class="dates">
				<span>Créé le <?php echo date($config["format"]["date"].' à '.$config["format"]["time"],strtotime($rec["creation"])) ?></span>
				<span>Modifié le <?php echo date($config["format"]["date"].' à '.$config["format"]["time"],strtotime($rec["modification"])) ?></span>
			</p>
			<?php } ?>
		</div>
	</div>
	<div class="details">
		<p class="titre">Détails techniques</p>
		<div class="clip">
			<div>
			  <?php if ( $action != $actions['view'] || $rec['jauge_min'] || $rec['jauge_max'] ): ?>
			  <p class="jauge">
			    <span>Jauge&nbsp;:</span>
			    <span>de <?php
			      $name = 'jauge_min';
			  	  printField(	"field[".($name)."]",$rec[$name],$default[$name],10,3,false,NULL,NULL,false);
			  	?></span>
			  	<span>à <?php
			      $name = 'jauge_max';
			  	  printField(	"field[".($name)."]",$rec[$name],$default[$name],10,3,false,NULL,NULL,false);
			    ?></span>
			    <span>places</span>
			  </p>
			  <?php endif; ?>
				<p class="dimsalle">
					<span>Dim. salle&nbsp;:</span>
					<?php
						for ( $i = 0 ; $i < 3 ; $i++ )
						{
							echo '<span>';
							$name = "dimensions_salle";
							echo $rec[$name][$i] || $action != $actions["view"] ? $default["dim"][$i].': ' : '';
							printField(	"field[".($name)."][]",$rec[$name][$i],
									$default[$name][$i],10,3,false,NULL,NULL,false);
							echo $rec[$name][$i] || $action != $actions["view"] ? "m" : "";
							echo '</span>';
						}
					?>
				</p><p class="dimscene">
					<span>Dim. scène&nbsp;:</span>
					<?php
						for ( $i = 0 ; $i < 3 ; $i++ )
						{
							echo '<span>';
							$name = "dimensions_scene";
							echo $rec[$name][$i] || $action != $actions["view"] ? $default["dim"][$i].': ' : '';
							printField(	"field[".($name)."][]",$rec[$name][$i],
									$default[$name][$i],10,3,false,NULL,NULL,false);
							echo $rec[$name][$i] || $action != $actions["view"] ? "m" : "";
							echo '</span>';
						}
					?>
				</p>
				<p class="amperage">
					<span>Ampérage&nbsp;:</span>
					<span><?php
						printField("field[".($name = "amperage")."]",$rec[$name],$default[$name],10,3);
						echo $rec[$name] || $action != $actions["view"] ? "A" : "N/A";
					?></span>
				</p>
			</div>
			<p class="noir">
			<?php if ( $action != $actions["view"] ) { ?>
				<span>Le noir est possible&nbsp;?</span>
				<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
					<input	type="radio" name="field[noir_possible][value]" value="t" onclick="javascript: ttt_spanCheckBox(this);"
						<?php if ( $rec["noir_possible"] == 't' ) echo 'checked="checked"';?> />
					Oui
				</span>
				<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
					<input	type="radio" name="field[noir_possible][value]" value="f" onclick="javascript: ttt_spanCheckBox(this);"
						<?php if ( $rec["noir_possible"] == 'f' ) echo 'checked="checked"';?> />
					Non
				</span>
			<?php }	else echo '<span>'.($rec["noir_possible"] == 't' ? "Le noir est possible" : "Le noir n'est pas possible").'</span>'; ?>
			</p>
			<p class="gradins">
			<?php if ( $action != $actions["view"] ) { ?>
				<span>La salle dispose de gradins&nbsp;?</span>
				<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
					<input	type="radio" name="field[gradins][value]" value="t" onclick="javascript: ttt_spanCheckBox(this);"
						<?php if ( $rec["gradins"] == 't' ) echo 'checked="checked"';?> />
					Oui
				</span>
				<span class="onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
					<input	type="radio" name="field[gradins][value]" value="f" onclick="javascript: ttt_spanCheckBox(this);"
						<?php if ( $rec["gradins"] == 'f' ) echo 'checked="checked"';?> />
					Non
				</span>
			<?php }	else echo '<span>'.($rec["gradins"] == 't' ? "La salle dispose de gradins" : "La salle ne dispose pas de gradins").'</span>'; ?>
			</p>
		</div>
	</div>
	<div class="gestion">
		<p class="titre">Gestion de la salle</p>
		<div class="clip">
			<p><?php
				if ( $action != $actions["view"] )
				{
					$query	= " SELECT * FROM org_categorie ORDER BY libelle";
					$orgcat	= new bdRequest($bd,$query);
					echo '<select name="categorie" onchange="javascript: annu_orgcategorie(this.parentNode);">';
					echo '<option value="">-les categories-</option>';
					echo '<option value="NULL">-sans categorie-</option>';
					while ( $cat = $orgcat->getRecordNext() )
						echo '<option value="'.intval($cat["id"]).'">'.htmlsecure($cat["libelle"]).'</option>';
					$orgcat->free();
					
					$query	= " SELECT *
						    FROM organisme_categorie
						    ORDER BY catdesc, nom, ville";
					$orgcat	= new bdRequest($bd,$query);
					echo '<select name="field[organisme][value]">';
					echo '<option value="">-les organismes-</option>';
					while ( $org = $orgcat->getRecordNext() )
						echo '<option value="'.intval($org["id"]).'" '.($rec["organisme"] == $org["id"] ? 'selected="selected"' : '').'>'.htmlsecure($org["nom"].' ('.$org["ville"].')').'</option>';
					echo '</select>';
					$orgcat->free();
				}
				else	echo 'Organismes&nbsp;: '.($rec["orgid"] ? '<a href="org/fiche.php?id='.intval($rec["orgid"]).'&view">'.htmlsecure($rec["orgnom"]).'</a>'.htmlsecure(' ('.$rec["orgville"].')') : '');
			?></p>
			<p><?php
				if ( $action != $actions["view"] )
				{
					$query	= " SELECT *
						    FROM personne_properso AS personne
						    WHERE LOWER(fcttype) = LOWER('".$fctregisseur."')";
					$pers	= new bdRequest($bd,$query);
					echo '<select name="field[regisseur][value]">';
					echo '<option value="">-les régisseurs-</option>';
					while ( $ppl = $pers->getRecordNext() )
						echo '<option value="'.intval($ppl["id"]).'" '.($ppl["regisseur"] == $rec["id"] && $rec['id'] > 0 ? 'selected="selected"' : '').'>'.htmlsecure($ppl["titre"].' '.$ppl["nom"].' '.$ppl["prenom"]).'</option>';
					echo '</select>';
					$pers->free();
				}
				else	echo 'Régisseur&nbsp;: '.($rec["persid"] ? '<a href="ann/fiche.php?id='.intval($rec["persid"]).'&view">'.htmlsecure($rec["perstitre"].' '.$rec["persnom"].' '.$rec["persprenom"].' ('.$rec["perstel"].')') : "");
			?></p>
		</div>
	</div>
	<?php if ( $config["ticket"]["placement"] && ( is_readable("plans/salle-".$id.".png") || $action != $actions["view"] ) ) { ?>
	<div class="places">
		<p class="titre">Placement</p>
		<div class="clip">
			<p class="loadmap">
				<span>Plan&nbsp;:</span>
				<?php	if ( $action != $actions["view"] ) { ?>
				<span><input disabled="disabled" type="checkbox" name="delmap" value="true" title="vider le plan de la salle existant" /></span>
				<span><input disabled="disabled" type="file" name="loadmap" /></span>
				<?php
					}
					if ( is_readable("plans/salle-".$id.".png") )
					{
				?>
					<span><a href="evt/infos/plans/salle.php/<?php echo $id ?>" target="salle">voir...</a></span>
				<?php	} ?>
			</p>
		</div>
	</div>
	<?php } ?>
	<?php
		if ( $action != $actions["view"] )
			echo '<input type="submit" value="Valider" name="submit" />';
	?>
	</form>
	<?php if ( $action == $actions["view"] ) { ?>
	<div class="manifs">
		<p class="titre">Manifestations</p>
		<div class="clip"><?php
			$query	= " SELECT *
				    FROM info_resa
				    WHERE  siteid = ".$id."
				      AND ( date >= NOW() - '1 month'::interval )
				      AND spaceid ".($user->evtspace ? ' = '.$user->evtspace : 'IS NULL')."
				    ORDER BY date, nom";
			$manifestations = new bdRequest($bd,$query);
			for ( $i = 0 ; $manif = $manifestations->getRecordNext() ; $i++ )
			{
				echo '<div '.($i == 0 ? 'id="manifmodel"' : '').' class="'.htmlsecure($manif["colorname"]).'">';
				echo '<a name="manif'.$manif["id"].'"></a>';
				echo '<input type="hidden" name="manif[manifid][][value]" value="'.$manif["id"].'" />';
				echo '<p>';
					echo '<span class="cell">Évènement:</span>';
					echo '<span class="cell">';
					echo '<a href="evt/infos/manif.php?evtid='.$manif["id"].'&id='.$manif["manifid"].'&view">'.htmlsecure($manif["nom"]).'</a><br/>';
					echo ' ('.htmlsecure($manif["catdesc"].' - '.$manif["typedesc"]).')';
					echo '</span>';
				echo "</p><p>";
					echo '<span class="cell">Date:</span>';
					echo '<span class="cell">';
					echo $config["dates"]["dotw"][intval(date('w',strtotime($manif["date"])))].' '.date($config["format"]["date"].' à '.$config["format"]["ltltime"],strtotime($manif["date"]));
					echo '</span>';
				echo '</p><p>';
					echo '<span class="cell">Duree:</span>';
					echo '<span class="cell">';
					echo htmlsecure(substr($manif["duree"],0,5));
					echo '</span>';
				echo '</p><p class="jauge">';
					echo '<span class="cell">Jauge:</span>';
					echo '<span class="cell">';
					printJauge(intval($manif["jauge"]),intval($manif["preresas"]),intval($manif["resas"]),140,intval($manif["commandes"]),200);
					echo '</span>';
				echo '</p><p class="description">';
					echo '<span class="cell">Description:</span>';
					echo '<span class="cell">';
					echo htmlsecure($manif["manifdesc"]);
					echo '</span>';
				echo "</p></div>";
			}
		?></div>
	</div>
<?php
	} // if ( $action == $actions["view"] )
	} // if ( $action != $actions["del"] )
	else
	{
?>
		<h2>Retirer l'évènement "<?php echo $rec["nom"] ?>" de l'annuaire ?</h2>
		<form name="formu" class="del" method="get" action="<?php echo $_SERVER["PHP_SELF"]?>">
			<input type="hidden" name="del" value="" />
			<input type="hidden" name="name" value="<?php echo htmlsecure($rec["nom"]) ?>" />
			<input type="hidden" name="id" value="<?php echo intval($rec["id"]) ?>" />
			<p>
				<input type="checkbox" name="deep" value="deep" />
				Suppression complète (avec les manifestations liées... non conseillé) ?
			</p>
			<p>
				Êtes-vous sûr ?
				<input type="submit" name="oui" value="oui" />
				<input type="submit" name="oui" value="non" />
			</p>
		</form>
<?php	} ?>
</div>
<?php
	includeLib("footer");
	$bd->free()
?>
