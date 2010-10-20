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
	$css[] = 'evt/styles/checklist.css';
	includeClass("bdRequest/array");
	includeLib("ttt");
	includeLib("actions");
	includeLib("jauge");
	includeJS("ajax");
	includeJS("ttt");
	includeJS("annu");
	includeJS("group","evt");
	includeJS("jquery");
	includeJS("jquery.contact");
	includeJS("infosweb","evt");
	
	if ( isset($_GET["del"]) )
		$action = $actions["del"];
	$evt = true;
	$id = intval($_GET["id"]);
	$class = "evt";
	
	$mod = $user->evtlevel >= $config["evt"]["right"]["mod"];
	
	// Suppression
	if ( $action == $actions["del"] && $mod )
	if ( $id > 0 )
	{
		if ( $_GET["oui"] == "oui" )
		if ( $bd->delRecordsSimple("evenement",array("id" => $id)) )
		{
			$user->addAlert($msg = "Évènement supprimé");
			$nav->redirect("./?s=".substr($_GET["name"],0,1),$msg);
		}
		else
		{
			$user->addAlert("Impossible de supprimer l'évènement.");
			$action = $actions["view"];
		}
	} // if ( $id > 0 )
	else	$action = $actions["view"];
	
	$css[] = "evt/styles/jauge.css";
	$css[] = "evt/styles/colors.css.php";
	
	includeLib("headers");
?>
<?php
	$default = array();
	$default["nom"]		= "-nom du spectacle-";
	$default["petitnom"]	= "-nom pour les billets-";
	$default["description"]	= "-petite description-";
	$default["typedesc"]	= "-genre de spectacle-";
	$default["duree"]	= "-HH:MM-";
	$default["date"]	= "-AAAA-MM-JJ HH:MM-";
	$default["age_min"]	= "-min-";
	$default["age_max"]	= "-max-";
	$default["code"]	= "-F3-";
	$default["jauge"]	= "-jauge-";
	$default["tarifweb"]	= "-6-";
	$default["tarifwebgroup"] = "-4-";
	
	$default["textede_lbl"]	= '-Label ~Texte de~-';
	$default["textede"]	= "-Jean Martin-";
	$default["mscene_lbl"]	= '-Label ~Mise en scène~-';
	$default["mscene"]	= "-Florence Thomas-";
	
	$query = "SELECT * FROM evt_categorie ORDER BY libelle";
	$evtcat = new bdRequest($bd,$query);
	$query = "SELECT * FROM org_categorie ORDER BY libelle";
	$orgcat = new bdRequest($bd,$query);
	$query = "SELECT * FROM organisme_categorie";
	$organismes = new bdRequest($bd,$query);
	
	// Enregistrement d'un nouvel évènement / modification d'un evt
	$fields = $_POST["field"];
	if ( $fields["nom"]["value"] && $fields["nom"]["value"] != $fields["nom"]["default"] && $mod )
	{
		$rec = array();
		foreach ( array("organisme1","organisme2","organisme3","nom","petitnom","description","typedesc",
				"categorie","mscene","mscene_lbl","textede","textede_lbl","duree",
				"ages","tarifweb","tarifwebgroup","imageurl","extradesc","extraspec","code","metaevt") as $value )
			$rec[$value] = NULL;
		
		foreach ( $fields as $key => $value )
		if ( $value["value"] && $value["value"] != $value["default"] )
			$rec[$key] = $value["value"];
		
		if ( $rec["age_max"] || $rec["age_min"] )
		{
			$rec["ages"] = array();
			$rec["ages"][] = $rec["age_min"] != "" ? floatval($rec["age_min"]) : 0;
			if ( $rec["age_max"] != "" ) $rec["ages"][] = floatval($rec["age_max"]);
			unset($rec["age_min"]);
			unset($rec["age_max"]);
		}
		$rec["modification"] = date("Y-m-d H:i:s");
		
		$intkeys = array("categorie","organisme1","organisme2","organisme3");
		foreach ( $intkeys as $value )
			if ( $rec[$value] ) $rec[$value] = intval($rec[$value]);
		
		if ( $id <= 0 )
		{
			if ( $bd->addRecord("evenement",$rec) )
				$id = $bd->getLastSerial("evenement","id");
			else	$user->addAlert("L'évènement n'a pu être créé");
		}
		elseif ( !$bd->updateRecordsSimple("evenement",array('id' => $id),$rec) )
			$user->addAlert("L'évènement n'a pu être modifié");
	}
	
	// La consultation
	$rec = false;
	if ( $id > 0 && $action != $actions["add"] )
	{
		$query = " SELECT evt.*, (SELECT libelle FROM evt_categorie WHERE id = evt.categorie) AS catdesc
			   FROM evenement AS evt
			   WHERE evt.id = ".$id;
		$request = new arrayBdRequest($bd,$query);
		
		if ( $request->countRecords() == 0 )
		{
			$request->free();
			$user->addAlert("L'évènement recherché n'existe pas");
			$action = $actions["add"];
		}
		else	$rec = $request->getRecordNext();
		
		if ( is_array($rec["ages"]) )
		{
			$rec["age_min"] = $rec["ages"][0] ? floatval($rec["ages"][0]) : "";
			$rec["age_max"] = $rec["ages"][1] ? floatval($rec["ages"][1]) : "";
			unset($rec["ages"]);
		}
	}
	
	// Les manifestations de l'évenement
	if ( $id > 0 && $mod )
	if (is_array($_POST["manif"]))
	{
		$manif = $_POST["manif"];
		
		if ( is_array($manif["delmanif"]) )
		for ( $i = 0 ; $i < count($manif["delmanif"]) ; $i++ )
		{
			if ( $manif["delmanif"][$i]["value"] )
			if ( !@$bd->delRecordsSimple("manifestation",array("id" => intval($manif["delmanif"][$i]["value"]))) )
				$user->addAlert("Impossible de supprimer la manifestation souhaitée (n°".intval($manif["delmanif"][$i]["value"]).")");
		}
		
		for ( $err = $i = 0 ; $i < count($manif["date"]) ; $i++ )
		{
			if ( $manif["date"][$i]["value"] && $manif["date"][$i]["value"] != $default["date"]
			  && intval($manif["site"][$i]["value"]) > 0 )
			{
				$arr = array();
				$arr["siteid"]			= intval($manif["site"][$i]);
				$arr["date"]			= $manif["date"][$i]["value"];
				if ( $manif["duree"][$i]["value"] && $manif["duree"][$i]["value"] != $default["duree"] )
					$arr["duree"]		= $manif["duree"][$i]["value"];
				elseif ( $rec["duree"] )
					$arr["duree"]		= $rec["duree"];
				if ( $manif["description"][$i]["value"] && $manif["description"][$i]["value"] != $default["description"] )
					$arr["description"]	= $manif["description"][$i]["value"];
				if ( $manif["jauge"][$i]["value"] && $manif["jauge"][$i]["value"] != $default["jauge"] && !$user->evtspace )
					$arr["jauge"]		= intval($manif["jauge"][$i]["value"]);
		    
  	  	if ( in_array('vel',$config['mods']) )
	  	    $arr['vel'] = $manif['vel'][$i]['value'] == 'yes' ? 't' : 'f';
				
				// Modification d'une manif
				if ( intval($manif["manifid"][$i]["value"]) > 0 )
     	  {
					// mises à NULL
					foreach ( array("duree","description") as $value )
						if ( !isset($arr[$value]) ) $arr[$value] = NULL;
					
					$arr["id"]		= intval($manif["manifid"][$i]["value"]);
					if ( !$bd->updateRecordsSimple("manifestation", array("id" => $arr['id']), $arr) )
						$user->addAlert("Impossible de modifier la manifestation du ".htmlsecure($manif["date"][$i]["value"].""));
				}
				else // ajout
				{
					$arr["evtid"]		= $id;
					$arr["txtva"]		= $rec["txtva"] ? $rec["txtva"] : $config["compta"]["defaulttva"];
					if ( !@$bd->addRecord("manifestation",$arr) )
						$user->addAlert("Impossible d'ajouter la manifestation du ".htmlsecure($manif["date"][$i]["value"].""));
				  else
				    $arr['id'] = $bd->getLastSerial('id','manifestation');
				}
				
				// la jauge lié à l'espace
				if ( $user->evtspace )
				  $bd->addOrUpdateRecord('space_manifestation',array('spaceid' => $user->evtspace, 'manifid' => $arr['id']),array('jauge' => intval($manif["jauge"][$i]["value"])));
				
			} // condition de modif/crea d'une manifestation
			else $err++;
		} // for ( $i = 0 ; $i < count($manif["date"]) ; $i++ )
		// trop de msgs d'err
		// if ( $err > 0 ) $user->addAlert($err." erreurs lors de la création ou la modification de manifestations pour cause de manque d'informations");
	}
	
	// si on n'a aucun droit autre que de consulter :
	if ( !$mod && $action != $actions["view"] )
	{
		$user->addAlert("Vous avez seulement le droit de consulter cette fiche");
		$action = $actions["view"];
	}
?>
<script type="text/javascript">
$(document).ready(function(){
  function imageurl_show()
  {
    $('#imageurl').parent().find('img.images').remove();
    urls = false;
    if ( $('imageurl').length > 0 )
      urls = $('#imageurl').val().split(';');
    for ( i = 0 ; i < urls.length ; i++ )
      $('#imageurl').parent().append('<img class="images" src="'+urls[i]+'" alt="img" />');
  }
  
  imageurl_show();
  $('#imageurl').blur(imageurl_show);
  $('#imageurl').keypress(function(e){
    if ( e.which = 59 )
      imageurl_show();
  });
});
</script>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
<?php
	if ( $action != $actions["del"] )
	{
?>
<div id="waiting">Calcul...</div>
<form class="evt" name="formu" method="post" action="<?php echo htmlsecure($_SERVER["PHP_SELF"]).'?'.($action != $actions["add"] ? "id=".$id."&" : "")."view"; ?>">
	<fieldset class="hidden"><?php // les valeurs par défaut
		foreach ( $default as $key => $value ) echo '<input type="hidden" name="def'.htmlsecure($key).'" value="'.htmlsecure($value).'" />';
	?></fieldset>
	<h2>Fiche évènement</h2>
	<div class="infos">
		<p class="titre">Informations générales</p>
		<div class="clip">
			<p class="global"><?php
				echo '<span class="nom">';
				printField("field[".($name = "nom")."]",$rec[$name],$default[$name],255,30);
				echo '</span><span class="petitnom">';
				printField("field[".($name = "petitnom")."]",$rec[$name],$default[$name],$config["ticket"]["titlemaxchars"],24);
				echo '</span>';
			?></p>
			<div class="desc"><?php printField("field[".($name = "description")."]",$rec[$name],$default[$name],NULL,45,true); ?></div>
			<p><?php
				$evtcat->firstRecord();
				if ( $action != $actions["view"] && $mod )
				{
					echo '<select name="field[categorie][value]"><option value="">-toute catégorie-</option><option value="NULL">-sans catégorie-</option>';
					while ( $cat = $evtcat->getRecordNext() )
						echo '<option value="'.intval($cat["id"]).'" '.($cat["id"] == $rec["categorie"] ? 'selected="selected"' : '').'>'.htmlsecure($cat["libelle"]).'</option>';
					echo '</select>';
				}
				elseif ( $rec["catdesc"] )
				echo htmlsecure($rec["catdesc"]).($rec["catdesc"] && $rec["typedesc"] ? " - " : "");
				printField("field[".($name = "typedesc")."]",$rec[$name],$default[$name],255);
			?></p>
			<p class="metaevt">Programmation: <?php
				if ( $action != $actions["view"] )
				{
					$query	= " SELECT str, false AS selected
						    FROM str_model
						    WHERE usage = 'metaevt'";
					if ( $rec["metaevt"] )
					$query .= "   AND str != '".pg_escape_string($rec["metaevt"])."'
						   UNION
					            SELECT '".pg_escape_string($rec["metaevt"])."' AS str, true AS selected";
					$query .= " ORDER BY str";
					$metaevt = new bdRequest($bd,$query);
					
					echo '<select name="field[metaevt][value]">';
					echo '<option value="">-Meta-évènement lié-</option>';
					while ( $evt = $metaevt->getRecordNext() )
					{
						echo '<option value="'.htmlsecure($evt["str"]).'" '.($evt["selected"] == 't' ? 'selected="selected"' : '').'>';
						echo htmlsecure($evt["str"]).'</option>';
					}
					echo '</select>';
					
					$metaevt->free();
				}
				else	echo htmlsecure($rec["metaevt"]);
			?></p>
			<p class="ages"><span><?php
				if ( $action != $actions["view"] || $rec["age_min"] )
				{
					echo "À partir de ";
					printField("field[".($name = "age_min")."]",$rec[$name],$default[$name],5,5);
					echo ' ans';
					if ( $rec[$name] < 2 && $rec[$name] > 0 ) echo ' ('.(round($rec[$name]*12)).' mois)';
				}
				if ( $action != $actions["view"] || ( $rec["age_max"] && $rec["age_min"] ) )
					echo ", ";
				if ( $action != $actions["view"] || $rec["age_max"] )
				{
					echo "Jusqu'à ";
					printField("field[".($name = "age_max")."]",$rec[$name],$default[$name],5,5);
					echo ' ans';
					if ( $rec[$name] < 2 && $rec[$name] > 0 ) echo ' ('.(round($rec[$name]*12)).' mois)';
				}
				echo "</span>";
			?></p>
			<p><?php
				$name = "duree";
				if ( $action != $actions["view"] || $rec[$name] ) echo "Durée: ";
				printField("field[".$name."]",substr($rec[$name],0,5),$default[$name],40);
			?></p>
			<p><span><?php
				$name = "code";
				if ( $action != $actions["view"] || $rec[$name] ) echo "Code: ";
				printField("field[".$name."]",$rec[$name],$default[$name],5,5);
			?></span></p>
		</div>
	</div>
	<div class="creation">
		<p class="titre">Création</p>
		<div class="clip">
		<?php
			if ( $action == $actions["view"] )
			{
				$cond = array();
				for ( $i = 0 ; $i < 3 ; $i++ )
					if ( $rec["organisme".($i+1)] )
						$cond[] = $rec["organisme".($i+1)];
				
				$query	= " SELECT *
					    FROM organisme_categorie
					    WHERE id = NULL ";
				if ( count($cond) > 0 )
					$query .= " OR id = ".implode(" OR id = ",$cond);
				$orgs	= new bdRequest($bd,$query);
				for ( $i = 0 ; $org = $orgs->getRecordNext() ; $i++ )
				{
					echo '<p class="orgview">';
					echo $i == 0 ? "<span>organismes:</span>" : "<span> </span>";
					echo '<span>';
					echo '<a href="org/fiche.php?id='.intval($org["id"]).'&view">'.htmlsecure($org['nom']).'</a>';
					echo htmlsecure(' ('.$org["ville"].')');
					echo '</span>';
					echo '</p>';
				}
			}
			else for ( $i = 0 ; $i < 3 ; $i++ )
			{
				echo '<p class="orged">';
				echo $i == 0 ? "<span>organismes:</span>" : "<span> </span>";
				echo '<span>';
				echo '<select name="" onchange="javascript: annu_orgcategorie(this.parentNode);"><option value="">-toute catégorie-</option><option value="NULL">-Sans categorie-</option>';
				$orgcat->firstRecord();
				while ( $cat = $orgcat->getRecordNext() )
					echo '<option value="'.intval($cat["id"]).'">'.htmlsecure($cat["libelle"]).'</option>';
				echo '</select>';
				
				$organismes->firstRecord();
				echo '<select name="field[organisme'.($i+1).'][value]"><option value="">-les organismes-</option>';
				while ( $org = $organismes->getRecordNext() )
				{
					echo '<option value="'.intval($org["id"]).'" '.($rec["organisme".($i+1)] == intval($org["id"]) ? ' selected="selected"' : '').'>';
					echo htmlsecure($org['nom'].' ('.$org["ville"].')');
					echo '</option>';
				}
				echo '</select>';
				echo '</span>';
				echo '</p>';
			}
		?>
		<p><span><?php
			if ( $action != $actions["view"] ) echo 'Auteur:</span><span>';
			printField("field[".($name = "textede_lbl")."]",$rec[$name],$default[$name],255);
			if ( $action == $actions["view"] ) echo $rec["textede_lbl"] ? ': </span><span>' : '</span><span>';
			printField("field[".($name = "textede")."]",$rec[$name],$default[$name],255);
		?></span></p>
		<p><span><?php
			if ( $action != $actions["view"] ) echo 'Montage:</span><span>';
			printField("field[".($name = "mscene_lbl")."]",$rec[$name],$default[$name],255);
			if ( $action == $actions["view"] ) echo $rec["mscene_lbl"] ? ': </span><span>' : '</span><span>';
			printField("field[".($name = "mscene")."]",$rec[$name],$default[$name],255);
		?></span></p>
		</div>
	</div>
	<?php if ( $config['evt']['ext']['checklist'] ): ?>
	<div class="checklist jqslide jqhide">
	  <p class="titre">Checklist</p>
	  <div class="clip">
	    <?php includePage('checklist'); ?>
	  </div>
	</div>
	<?php endif; ?>
	<?php if ( $config["evt"]["ext"]["web"] ) { ?>
	<div class="web jqslide jqhide">
		<p class="titre">Infos web</p>
		<div class="clip">
			<script type="text/javascript" src="libs/tinymce/tiny_mce.js"></script>
			<script type="text/javascript">
				tinyMCE.init({
					mode : "textareas",
					editor_deselector : "noeditor",
					language: "fr",
					theme: "advanced",
					theme_advanced_buttons1 : "bold,italic,underline,|,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,link,unlink",
					theme_advanced_buttons2 : "fontsizeselect,formatselect,|,hr,|,undo,redo,|,pastetext,pasteword,selectall",
					theme_advanced_buttons3 : "",
					theme_advanced_toolbar_location : "bottom",
					theme_advanced_toolbar_align : "center",
          plugins : "paste",
          paste_use_dialog : true,
          paste_auto_cleanup_on_paste : true,
          paste_convert_headers_to_strong : false,
          paste_strip_class_attributes : "all",
          paste_remove_spans : true,
          paste_remove_styles : true,					
				});
			</script>
			<p><?php
				$name = "tarifweb";
				if ( $action != $actions["view"] || $rec[$name] ) echo "Tarif web: ";
				printField("field[".$name."]",floatval($rec[$name]),$default[$name],3,1);
				if ( $action != $actions["view"] || $rec[$name] ) echo '€ ';
				$name = "tarifwebgroup";
				printField("field[".$name."]",$rec[$name] ? floatval($rec[$name]) : "",$default[$name],3,1);
				if ( $action != $actions["view"] || $rec[$name] ) echo '€';
			?>
			</p><p>Image(s)<?php echo $action == $actions['edit'] ? ' (sep. ";")' : '' ?>: <?php
				if ( $action == $actions["view"] )
				{
					$imgs = split(";",$rec["imageurl"]);
					foreach ( $imgs as $img ) 
						echo '<img src="'.htmlspecialchars($img).'" class="images" alt="image du spectacle" />';
				}
				else	echo '<textarea id="imageurl" name="field[imageurl][value]" class="noeditor" style="width: 250px;">'.htmlspecialchars($rec["imageurl"]).'</textarea>';
			?></p>
			<div class="extraspec"><?php
				$name = "extraspec";
				echo '<span class="titre">'."Autour de l'évènement</span>";
				printField("field[".$name."]",$rec[$name],$default[$name],9,90,true,' ',' ');
			?></div>
			<div class="extradesc"><?php
				$name = "extradesc";
				echo '<span class="titre">'."Distribution</span>";
				printField("field[".$name."]",$rec[$name],$default[$name],9,90,true,' ',' ');
			?></div>
		</div>
	</div>
	<?php } ?>
	<div class="manifs">
		<p class="titre">Les dates</p>
		<div class="clip">
			<?php if ( $action == $actions["view"] ): ?>
			<?php if ( $config['evt']['spaces'] ): ?>
			<form class="spaces" title="Accessible uniquement si vous disposez des droits suffisants" action="" method="post">
			  <input type="checkbox" name="space" value="all" <?php if ( $_POST['space'] == 'all' ) echo 'checked="checked"' ?> onchange="javascript: submit();" /> <label for="space">Voir tous les espaces</label>
			</form>
			<?php endif; ?>
			<?php else: ?>
			<p class="add">
				<span class="cell">
					<input type="button" onclick="javascript: ttt_addmanif(document.getElementById('manifmodel'));" value="+" name="add"/>
					<span class="desc">nouvelle manifestation</span>
					<input type="hidden" name="manif[delmanif][][value]" id="delmanif" value="" />
				</span>
			</p>
			<?php endif; ?>
			<?php
				// la liste des sites dispos
				$query	= " SELECT id, ville, nom
					    FROM site
					    WHERE active = 't'
					    ORDER BY ville, nom";
				$sites = new bdRequest($bd,$query);
				
				// la liste des manifs
  		  $select = array(
			    'id', 'organisme1', 'organisme2', 'organisme3', 'nom', 'description',
			    'categorie', 'typedesc', 'mscene', 'mscene_lbl', 'textede', 'textede_lbl', 'duree', 'ages', 'code', 'creation', 'modification', 'catdesc',
			    'manifid', 'date', 'vel', 'manifdesc',
			    'siteid', 'sitenom', 'ville', 'cp', 'plnum', 'commandes', 'resas', 'preresas', 'deftva', 'txtva', 'colorname', 'color',
			  );
				if ( $_POST['space'] == 'all' )
          $query  = " SELECT ".implode(',',$select).", sum(jauge) as jauge, CASE WHEN date > now() - '1 day'::interval THEN 1 ELSE 2 END AS o, sum(jauge) = 0 AS last
                      FROM info_resa ir
                      WHERE ir.id = ".$id."
                      GROUP BY ".implode(',',$select)."
                      ORDER BY last,o,date,sitenom";
        else
          $query  = " SELECT ".implode(',',$select).", jauge, CASE WHEN date > now() - '1 day'::interval THEN 1 ELSE 2 END AS o, ir.jauge = 0 AS last
                      FROM info_resa ir
                      WHERE ir.id = ".$id."
                        AND ".($user->evtspace ? 'ir.spaceid = '.$user->evtspace : 'ir.spaceid IS NULL')."
                      ORDER BY last,o,date,sitenom";
				  
				if ( $action == $actions["add"] ) $query = NULL;
				$manifestations = new bdRequest($bd,$query);
				
				for ( $i = 0 ; ( $i == 0 && $action != $actions["view"] ) | ( $manif = $manifestations->getRecordNext() ) ; $i++ )
				{
					echo '<div '.($i == 0 ? 'id="manifmodel"' : '').' class="'.($action == $actions["view"] ? "view" : "edit").' '.htmlsecure($manif["colorname"]).'">';
					echo '<a name="manif'.$manif["id"].'"></a>';
					echo '<input type="hidden" name="manif[manifid][][value]" value="'.$manif["manifid"].'" />';
					if ( $action == $actions["view"] )
					{
						echo '<p class="url">';
						echo '<span class="cell"></span><span class="cell">';
						echo '<a href="evt/infos/manif.php?evtid='.$id.'&id='.intval($manif["manifid"]).'">Détails ...</a>';
						echo '<span class="desc">Édition des détails optionnels de la manifestation, comme les différents tarifs appliqués</span>';
						if ( $user->hasRight($config["right"]["group"]) )
						{
							echo ' | ';
							echo '<a onclick="javascript: createGroup('.intval($manif["manifid"]).',this);" class="noerror">Export</a>';
							echo '<span class="desc">'."Export des personnes présentes (reservations et pré-réservations) pour cette manifestation dans un groupe".'</span>';
							echo '<span class="error">'."Erreur".'</span>';
						}
						echo '</span>';
						echo '</p>';
					}
					echo '<p>';
						echo '<span class="cell st">Date'.($action != $actions["view"] ? '*' : '').':</span>';
						echo '<span class="cell">';
						printField("manif[".($name = "date")."][]",($action == $actions["view"] ? $config['dates']['dotw'][date('w',strtotime($manif["date"]))].' '.date($config["format"]["date"].' à '.$config["format"]["ltltime"],strtotime($manif["date"])) : $manif[$name]),$default[$name],255,NULL,false,NULL,NULL,false);
						echo '</span>';
					echo '</p><p>';
						echo '<span class="cell">Site'.($action != $actions["view"] ? '*' : '').':</span>';
						echo '<span class="cell">';
						if ( $action == $actions["view"] )
						{
							echo '<a href="evt/infos/salle.php?id='.$manif["siteid"].'&view">'.htmlsecure($manif["sitenom"]).'</a>';
							echo ' ('.htmlsecure(($manif["cp"] ? $manif["cp"].', ' : '').$manif["ville"]).')';
						}
						else
						{
							echo '<select name="manif[site][]">';
							echo '<option value="">-les lieux-</option>';
							$sites->firstRecord();
							while ( $site = $sites->getRecordNext() )
								echo '<option value="'.intval($site["id"]).'" '.(intval($site["id"]) == intval($manif["siteid"]) ? 'selected="selected"' : '').'>'.htmlsecure($site["ville"]).' - '.htmlsecure($site["nom"]).'</option>';
							echo '</select>';
						}
						echo '</span>';
					echo "</p><p>";
						echo '<span class="cell">Durée:</span>';
						echo '<span class="cell">';
						printField("manif[".($name = "duree")."][]",substr($manif[$name],0,5),$default[$name],255,NULL,false,NULL,NULL,false);
						echo '</span>';
  			  echo '</p><p class="jauge">';
						echo '<span class="cell">Jauge:</span>';
						echo '<span class="cell">';
						if ( $action != $actions["view"] )
						{
							printField("manif[".($name = "jauge")."][]",$manif["jauge"],$default[$name],10,6,false,NULL,NULL,false);
							echo " pl.";
						}
						else printJauge(intval($manif["jauge"]),intval($manif["preresas"]),intval($manif["resas"]),140,intval($manif["commandes"]),200);
						if ( $config["ticket"]["placement"] )
						{
							// on précise explicitement la manif à modifier (manif[plnum]['.$i.']) car en cas de case non cochée, ca brise la chaine des "[]" habituels
							echo ' <span class="plnum">'.($manif["plnum"] == 't' ? "num." : "libre").'</span>';
						}
						echo '</span>';
					if ( in_array('vel',$config['mods']) )
					{
				    echo '</p><p class="vel" title="Sélectionné pour la vente en ligne ?">';
				      echo '<span class="cell">VeL ?</span>';
						  echo '<span class="cell">';
						  if ( $action == $actions['view'] )
						    echo $manif['vel'] == 't' ? 'Oui' : 'Non';
						  else
						  {
						    echo '<input type="radio" name="manif[vel]['.$i.'][value]" value="yes" '.($manif['vel'] == 't' ? 'checked="checked"' : '').' />oui ';
  						  echo '<input type="radio" name="manif[vel]['.$i.'][value]" value="no"  '.($manif['vel'] != 't' ? 'checked="checked"' : '').' />non';
  						}
  						echo '</span>';
				  }
					echo '</p><p class="description">';
						echo '<span class="cell">Desc.:</span>';
						echo '<span class="cell">';
						printField("manif[".($name = "description")."][]",$manif["manifdesc"],$default[$name],255,NULL,true,' ',' ',false,'class="noeditor"');
						echo '</span>';
					if ( $action != $actions["view"] )
					{
						echo '</p><p class="del">';
							echo '<span class="cell">';
							echo '<input type="button" onclick="javascript: '."ttt_delmanif(this.parentNode.parentNode.parentNode);".'" value="-" name="del"/>';
							echo '<span class="desc">retirer cette manifestation</span>';
							echo '</span>';
					}
					echo "</p></div>";
				} // for ( $i = 0 ; ( $i == 0 && $action != $actions["view"] ) | ( $manif = $manifestations->getRecordNext() ) ; $i++ )
				
				$manifestations->free();
				$sites->free();
			?>
		</div>
	</div>
	<?php
		if ( $action != $actions["view"] )
		{
			echo '<input type="submit" value="valider" name="submit" /> ';
			echo '<input type="submit" value="valider - revenir" name="submit" onclick="'."javascript: this.form.action += '&edit'; this.form.submit();".'" />';
		}
		echo '</form>';
	}
	else
	{
?>
		<h2>Retirer l'évènement "<?php echo $rec["nom"] ?>" de l'annuaire ?</h2>
		<form name="formu" class="del" method="get" action="<?php echo $_SERVER["PHP_SELF"]?>">
			<input type="hidden" name="del" value="" />
			<input type="hidden" name="name" value="<?php echo htmlsecure($rec["nom"]) ?>" />
			<input type="hidden" name="id" value="<?php echo intval($rec["id"]) ?>" />
			<p>
				Êtes-vous sûr ?
				<input type="submit" name="oui" value="oui" />
				<input type="submit" name="oui" value="non" />
			</p>
		</form>
<?php	} ?>
</div>
<?php
	$organismes->free();
	$orgcat->free();
	$evtcat->free();
	includeLib("footer");
	$bd->free()
?>
