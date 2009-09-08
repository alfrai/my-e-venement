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
	includeLib("ttt");
	includeJS("ttt");
	includeJS("ajax");
	includeJS("annu");
	includeJS("jquery");
	includeJS("jquery.contact");
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	$id = intval($_GET["id"]);
	
	// mise en forme des données
	$new = &$_POST["field"];
	$tel = &$_POST["tel"];
	
	if ( (( $id > 0 && $user->hasRight($config["right"]["edit"]) )||( $id <= 0 && $user->hasRight($config["right"]["add"]) ))
	  && $new["nom"]["value"]	!= "" && $new["nom"]["value"]		!= $new["nom"]["default"] )
	{
		// Modification de la personne à proprement parlé
		$arr = array();
		foreach ( $new as $key => $value )
		{
			if ( $value["value"] != $value["default"] && $value["value"] != "" )
				$arr[pg_escape_string($key)] = stripslashes($value["value"]);
			else	$arr[pg_escape_string($key)] = NULL;
		}
		$arr["nom"] = strtoupper($arr["nom"]);
		$arr["modification"] = date("Y-m-d H:i:s");
		
		if ( intval($_POST["id"]) > 0 && $id == $_POST["id"] )
			$ppl = $bd->updateRecordsSimple("personne",array("id" => intval($_POST["id"])),$arr);
		elseif ( $id == 0 )
		{
			$ppl = $bd->addRecord("personne",$arr);
			$id = intval($bd->getLastSerial("entite","id"));
			$action = $actions["view"];
		}
		
		if ( $id > 0 )
		{
			// gestion des numéro de téléphone
			$typedef = $_POST["typedefault"];
			$numdef  = $_POST["numdefault"];
			
			// nettoyage des vieux téléphones
			$bd->delRecordsSimple("telephone_personne",array("entiteid" => $id));
			for ( $i = count($tel["num"]) -1 ; isset($tel["num"][$i]["value"]) ; $i-- )
			{
				if ( $tel["num"][$i]["value"] != $numdef && $tel["num"][$i]["value"] != "" )
				{
					$arr = array();
					if ( $tel["type"][$i]["value"] != $typedef && $tel["type"][$i]["value"] != "" )
						$arr["type"] = pg_escape_string($tel["type"][$i]["value"]);
					$arr["numero"] = pg_escape_string($tel["num"][$i]["value"]);
					$arr["entiteid"] = $id;
					$bd->addRecord("telephone_personne",$arr);
				}
			} // for ( $i = count($tel["num"]) -1 ; isset($tel["num"][$i]["value"]) ; $i-- )
			
			// gestion des fonctions dans les organismes
			$srvdef = $_POST["srvdefault"];
			$fctdef = $_POST["fctdefault"];
			$emaildef = $_POST["emaildefault"];
			
			$org = $_POST["org"];
			for ( $i = 0 ; isset($org["org"][$i]["value"]) ; $i++ )
			{
				// Ajout
				if ( intval($org["org"][$i]["value"]) > 0 && intval($org["fctid"][$i]["value"]) <= 0 )
				{
					$arr = array();
					$arr["personneid"]		= $id;
					$arr["organismeid"]		= intval($org["org"][$i]["value"]);
					if ( $org["fct"][$i]["value"]	!= $fctdef && $org["fct"][$i]["value"] != "" )
						$arr["fonction"]	= substr($org["fct"][$i]["value"],0,255);
					if ( ($fcttype = intval($org["fcttype"][$i]["value"])) > 0 )
						$arr["type"]		= $fcttype;
					if ( $org["email"][$i]["value"] != $emaildef && $org["email"][$i]["value"] != "" )
						$arr["email"]		= substr($org["email"][$i]["value"],0,255);
					if ( $org["telephone"][$i]["value"] != $numdef && $org["telephone"][$i]["value"] != "" )
						$arr["telephone"]	= substr($org["telephone"][$i]["value"],0,40);
					if ( $org["service"][$i]["value"] != $srvdef && $org["service"][$i]["value"] != "" )
						$arr["service"]		= substr($org["service"][$i]["value"],0,255);
					if ( $org[$tmp = "description"][$i]["value"] != $default[$tmp] && $org[$tmp][$i]["value"] != "" )
						$arr[$tmp]		= $org[$tmp][$i]["value"];
					
					$bd->addRecord("org_personne",$arr);
				}
				
				// MAJ
				if ( intval($org["org"][$i]["value"]) > 0 && intval($org["fctid"][$i]["value"]) > 0 )
				{
					$arr = array();
					$arr["personneid"]	= $id;
					$arr["organismeid"]	= intval($org["org"][$i]["value"]);
					$arr["fonction"]	= $org["fct"][$i]["value"] != $fctdef && $org["fct"][$i]["value"] != ""
								? substr($org["fct"][$i]["value"],0,255)
								: NULL;
					$arr["type"]		= intval($org["fcttype"][$i]["value"]) > 0
								? intval($org["fcttype"][$i]["value"])
								: NULL;
					$arr["email"]		= $org["email"][$i]["value"] != $emaildef && $org["email"][$i]["value"] != ""
								? substr($org["email"][$i]["value"],0,255)
								: NULL;
					$arr["telephone"]	= $org["telephone"][$i]["value"] != $numdef && $org["telephone"][$i]["value"] != ""
								? substr($org["telephone"][$i]["value"],0,40)
								: NULL;
					$arr["service"]		= $org["service"][$i]["value"] != $srvdef && $org["service"][$i]["value"] != ""
								? substr($org["service"][$i]["value"],0,255)
								: NULL;
					$arr["description"]	= $org["description"][$i]["value"] != $default['description'] && $org["description"][$i]["value"] != ""
								? $org["description"][$i]["value"]
								: NULL;
					$bd->updateRecordsSimple("org_personne",
								 array(	"id" => intval($org["fctid"][$i]["value"]),
								 	"personneid" => $id ),
								 $arr);
				}
				
				// Suppression
				for ( $j = 0 ; isset($org["delfct"][$j]["value"]) ; $j++ )
				{
					$err = 0;
					if ( intval($org["delfct"][$j]["value"]) > 0 )
					{
						if( !$bd->delRecordsSimple(	"org_personne",
										array(	"id" => intval($org["delfct"][$j]["value"]),
											"personneid" => $id) ))
							$err++;
						if ( $err > 0 )	$user->addAlert("Impossible de supprimer les ".$err." fonction(s) voulue(s).");
					}
				}
			} // for ( $i = 0 ; isset($org["org"][$i]["value"]) ; $i++ )
			
			// gestion des groupes statiques
			if ( intval($_POST["grpstat"]["id"]) > 0 && $user->hasRight($config["right"]["group"]) )
			{
				$r = false;
				if ( intval($_POST["grpstat"]["fct"]) > 0 )
					$r = $bd->addRecord(	"groupe_fonctions",
								array(	"groupid"	=> intval($_POST["grpstat"]["id"]),
									"fonctionid"	=> intval($_POST["grpstat"]["fct"]),
								"included" => 't'));
				else	$r = $bd->addRecord("groupe_personnes",array("groupid" => intval($_POST["grpstat"]["id"]),"personneid" => $id,"included" => 't'));
				if ($r)	$user->addAlert("Le contact a bien été ajouté au groupe demandé");
				else	$user->addAlert("Echec de l'ajout du contact au groupe demandé");
			}
			if ( is_array($del = $_POST["grpdel"]["id"]) && $user->hasRight($config["right"]["group"]) )
			{
				foreach ( $del as $delid )
				if ( intval($delid) > 0 )
				$bd->delRecordsSimple("groupe_personnes",array("personneid" => $id, "groupid" => intval($delid)));
			}
			if ( is_array($del = $_POST["grpdel"]["fct"]) && $user->hasRight($config["right"]["group"]) )
			{
				foreach ( $del as $fctid => $delid )
				if ( intval($delid) > 0 && intval($fctid) > 0 )
				$bd->delRecordsSimple("groupe_fonctions",array("fonctionid" => $fctid, "groupid" => intval($delid)));
			}
			
			// gestion des dates de naissance des enfants
			if ( is_array($_POST["delchild"]) )
			foreach ( $_POST["delchild"] as $child )
			if ( intval($child)."" == $child."" )
				$bd->delRecordsSimple("child",array("personneid" => $id, "id" => $child));
			
			if ( is_array($_POST["child"]) )
			foreach ( $_POST["child"] as $child )
			if ( $child )
				$bd->addRecord("child",array("personneid" => $id, "birth" => $child));

		} // if ( $id > 0 )
		
	} // if ( isset($_POST["valid"]) )
	elseif ( isset($_POST["valid"]) )
	{
		$user->addAlert("Problème lors de la modification ou la création de votre fiche");
		$action = $actions["add"];
	}
	
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
	
	// Les valeurs "modèles"
	$typeDefault = "-type-";
	$numDefault = "-05 87 95 35 32-";
	$fctDefault = "-Chargé de Mission-";
	$emailDefault = "-drh@lgc.tld-";
	$srvDefault = "-Service Culturel-";
	$addrDefault = "29000 Quimper, France";
	
	// acquisition des données à afficher
	$request = false;
	if ( $id > 0 && $action != $actions["add"] )
	{
		$query	= " SELECT *
			    FROM personne
			    WHERE id = ".$id;
		$request = new bdRequest($bd,$query);
		
		if ( $request->countRecords() == 0 )
		{
			$request->free();
			$request = false;
		}
	}
	
	if ( $rec["npai"] == 't' )
		$class = "npai";
	
	// L'entete
	includeLib("headers");
	echo '<h1>'.$title.'</h1>';
	includeLib("tree-view");
	
	if ( !$request && $action != $actions["add"] )
	{
		$user->addAlert("La personne recherchée n'existe pas");
		$action = $actions["add"];
	}
	elseif ( $request )
	{
		$rec = $request->getRecord();
		$request->free();
	}
?>
<?php require('actions.php'); ?>
<div class="body">
<?php
	$query	= " SELECT str FROM str_model WHERE usage = 'titretype'";
	$titregen = new bdRequest($bd,$query);
?>
<form class="personne<?php if ( $rec["npai"] == 't' ) echo " npai"?>" name="formu" action="ann/fiche.php?id=<?php echo $action == $actions["add"] ? 0 : $id ?>&view" method="post">
	<div class="contact">
		<p class="titre">Contact</p>
		<input type="hidden" name="fctdefault" value="<?php echo htmlsecure($fctDefault) ?>" />
		<input type="hidden" name="emaildefault" value="<?php echo htmlsecure($emailDefault) ?>" />
		<input type="hidden" name="srvdefault" value="<?php echo htmlsecure($srvDefault) ?>" />
		<input type="hidden" name="typedefault" value="<?php echo htmlsecure($typeDefault) ?>" />
		<input type="hidden" name="numdefault" value="<?php echo htmlsecure($numDefault) ?>" />
		<input type="hidden" name="descdefault" value="<?php echo htmlsecure($default["description"]) ?>" />
		<input type="hidden" name="isnan" value="<?php echo htmlsecure("Veuillez saisir un numéro de téléphone valide") ?>" />
		<input type="hidden" name="id" value="<?php echo $id ?>" />
		<p class="global">
			<span>
				<?php
					if ( $action != $actions["add"] && $action != $actions["edit"] )
						echo htmlsecure($rec["titre"]);
					else
					{
				?>
				<select class="titre" name="field[titre][value]" id="focus">
					<option value=""></option>
					<?php
						while ( $titre = $titregen->getRecordNext('str') )
							echo '<option value="'.htmlsecure($titre).'" '.($rec["titre"] == $titre ? 'selected="selected"' : "").'>'.htmlsecure($titre).'</option>';
					?>
				</select>
				<?php	} ?>
			</span>
			<span><?php printField("field[".($name = "nom")."]",$action == $actions['add'] && $_GET['nom'] ? strtoupper($_GET['nom']) : $rec[$name],"-DUPORT-",127,15,NULL,NULL,"ttt_nomblur(this,'-DUPORT-')",NULL,'onkeyup="javascript: annu_search(this)"') ?></span>
			<span><?php printField("field[".($name = "prenom")."]",$rec[$name],"-Ilene-",255,15) ?></span>
		</p>
		<p class="adresse">
			<?php $address = trim($rec["adresse"].$rec["cp"].$rec["ville"]) ? $rec["adresse"].', '.$rec["cp"].' '.$rec["ville"].', '.$rec["pays"] : ''; ?>
			<span><?php printField("field[".($name = "adresse")."]",$rec[$name],"-3, rue du Stang-",NULL,NULL,true) ?></span>
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
				printField("field[".($name = "email")."]",$rec[$name],"-i.duport@dom.tld-",255);
				if ( $action == $actions["view"] ) echo '</a>';
			?>
			</span>
		</p>
		<p class="npai">
			<span onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(1));">
			<input type="hidden" name="field[npai][value]" value="f" />
			<?php
				if ( $action == $actions["edit"] )
				{
					echo '<input type="checkbox" name="field[npai][value]" value="t"';
					if ( $rec["npai"] == 't' ) echo ' checked="checked"';
					echo ' onclick="javascript: ttt_spanCheckBox(this)" />';
				}
				if ( $action == $actions["edit"] || $rec["npai"] == 't' )
					echo "<span>N'habite plus l'adresse indiquée</span>";
			?>
			</span>
		</p>
		<?php
			if ( $action != $actions["add"] )
			{
		?>
		<p class="dates">
			<span>Créé le <?php echo date($config["format"]["date"].' à '.$config["format"]["time"],strtotime($rec["creation"])) ?></span>
			<span>
				Modifié le
				<?php
					echo date($config["format"]["date"].' à '.$config["format"]["time"],
						$action == $actions["edit"] ? strtotime("now") : strtotime($rec["modification"]) );
				?>
			</span>
		</p>
		<?php	} ?>
	</div>
	<?php
		// googlemap
		if ( $action == $actions["view"] && $config["gmap"]["enable"] )
		{
			includeLib("googlemap");
			print_googlemap($address);
		}
	?>
	<div class="organismes jqslide">
		<p class="titre"><span></span>Organismes</p>
		<div class="clip">
		<?php
			if ( $action == $actions["add"] || $action == $actions["edit"] )
			{
				echo '<p class="add">';
				echo '<input type="button" onclick="javascript: '."ttt_addorg(document.getElementById('orgmodel'));".'" value="+" name="add"/>';
				echo '<span class="desc">nouvel organisme</span>';
				echo '</p>';
			}
		?>
		<input type="hidden" name="org[delfct][][value]" id="delfct" value="" />
		<?php
			$query		= " SELECT * FROM fonction ORDER BY libelle";
			$fonctions	= new bdRequest($bd,$query);
			$query		= " SELECT * FROM org_categorie ORDER BY libelle";
			$categories	= new bdRequest($bd,$query);
			$query		= " SELECT * FROM organisme_categorie ORDER BY nom, ville";
			$organismes	= new bdRequest($bd,$query);
			
			// Affichage des fonctions déjà existantes
			$query = "SELECT *
				  FROM personne_properso
				  WHERE id = ".$id."
				    AND fctorgid IS NOT NULL
				  ORDER BY orgnom,orgcat,orgville DESC";
			if ( $action == $actions["add"] ) $query = NULL;
			$request = new bdRequest($bd,$query);
			
			$properso = array();
			for ( $i = 0 ; ($rec = $request->getRecordNext()) || ($i == 0 && $action != $actions["view"]) ; $i++ )
			{
				if ( $rec )
				{
					$properso[intval($rec["fctorgid"])]["orgnom"] = $rec["orgnom"];
					$properso[intval($rec["fctorgid"])]["fonction"] = $rec["fctdesc"] ? $rec["fctdesc"] : $rec["fcttype"];
				}
		?>
		<div id="orgmodel">
		<p class="entite">
			<span class="typeinfo">organisme:</span>
			<span>
			<?php
				echo '<input type="hidden" name="org[fctid][][value]" value="'.$rec["fctorgid"].'" />';
				if ( $action == $actions["add"] || $action == $actions["edit"] )
				{
					global $orgid;
					$orgid = $rec["orgid"];
					includePage("../gen/linkorg");
				}
				else
				{
					echo '<a href="org/fiche.php?id='.$rec["orgid"].'&view">'.$rec["orgnom"].'</a> ';
					echo '('.$rec["orgcatdesc"].($rec["orgcatdesc"] && $rec["orgville"] ? ' - ' : '').$rec["orgville"].')';
				}
				
			?>
			</span>
		</p>
		<p class="fonction">
			<span class="typeinfo">fonction:</span>
			<span>
			<?php
				printField("org[fct][]",$rec["fctdesc"],$fctDefault,255,NULL,false,NULL,NULL,false);
				if ( $action == $actions["add"] || $action == $actions["edit"] )
				{
					echo ' <select name="org[fcttype][][value]">';
					echo '<option value="">-type de fonction-</option>';
					$fonctions->firstRecord();
					while ( $fct = $fonctions->getRecordNext() )
					{
						echo '<option value="'.intval($fct['id']).'" ';
						echo $fct['id'] == $rec["fctid"] ? 'selected="selected" >' : '>';
						echo htmlsecure($fct['libelle']);
						echo '</option>';
					}
					echo '</select>';
				}
				else	echo ' ('.htmlsecure($rec["fcttype"]).')';
			?>
			</span>
		</p>
		<p class="service">
			<span class="typeinfo">service:</span>
			<span><?php printField("org[service][]",$rec["service"],$srvDefault,255,NULL,false,NULL,NULL,false); ?></span>
		</p>
		<p class="contact">
			<span class="typeinfo">contact direct:</span>
			<span>
			<?php
				printField("org[telephone][]",$rec["protel"],$numDefault,40,NULL,false,NULL,NULL,false);
				if ( ($rec["protel"] && $rec["proemail"]) || $action != $actions["view"] )
					echo ', ';
				if ( $action == $actions["view"] ) echo '<a class="email" href="mailto:'.$rec["proemail"].'">';
				printField("org[email][]",$rec["proemail"],$emailDefault,255,NULL,false,NULL,NULL,false);
				if ( $action == $actions["view"] ) echo '</a>';
			?>
			</span>
		</p>
		<p class="description">
			<span class="typeinfo">description:</span>
			<span>
			<?php
				$tmp = "description";
				printField("org[".$tmp."][]",$rec[$tmp],$default[$tmp],NULL,NULL,true);
			?>
			</span>
		</p>
		<?php
			if ( $action == $actions["add"] || $action == $actions["edit"] )
			{
				echo '<p class="del">';
				echo '<input type="button" onclick="javascript: ttt_delorg(this.parentNode.parentNode);" value="-" name="del"/>';
				echo '<span class="desc">retirer cet organisme</span>';
				echo '</p>';
			}
		?>
		</div>
		<?php
			} // for ( $i = 0 ; $rec = $request->getRecordNext() || $i == 0 ; $i++ )
			$request->free();
			
			$fonctions->free();
			$organismes->free();
			$categories->free();
		?>
		</div>
		<?php if ( $action != $actions["view"] ) { ?>
		<p class="fichemaj">
			<input type="button" value="valider - revenir" name="valid" onclick="javascript: this.form.action += '&edit'; this.form.submit();" />
			<input type="submit" name="valid" value="valider" />
		</p>
		<?php } ?>
	</div>
	<div class="tel jqslide">
		<p class="titre">Téléphones</p>
		<div class="clip">
		<?php
			if ( $action == $actions["edit"] || $action == $actions["add"] )
			{
				$query  = " SELECT str AS type FROM str_model WHERE usage = 'teltype' ORDER BY str";
				$typegen = new bdRequest($bd,$query);
		?>
		<p id="telmodel" class="tel">
			<span class="type">
				<input	type="text" name="tel[type][][value]"
					class="exemple"
					value="<?php echo $typeDefault ?>"
					onfocus="javascript:ttt_onfocus(this,'<?php echo $typeDefault ?>');"
					onblur="javascript: ttt_onblur(this,'<?php echo $typeDefault ?>');"
					size="16" maxlength="127" />
				<select	name="tel[typegen][][value]" size="<?php echo $typegen->countRecords() + 1 ?>"
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
			} // if ( $action == $actions["edit"] || $action == $actions["add"] )
			
			$query	= " SELECT *
				    FROM telephone_personne
				    WHERE entiteid = ".$id;
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
				<select	name="tel[typegen][][value]" size="<?php echo $typegen->countRecords() + 1 ?>"
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
		?>
		</div>
	</div>
	<ul id="personnes"></ul>
	<?php
		// les enfants du contact
		$query = " SELECT * FROM child WHERE personneid = ".$id." ORDER BY birth";
		$request = new bdRequest($bd,$query);
		
		if ( $request->countRecords() > 0 || $action != $actions["view"] )
		{
	?>
	<div class="children jqslide">
		<p class="titre">Enfants</p>
		<div class="clip">
			<p class="content">Années de naissance&nbsp;:</p>
			<p class="content">
			<?php
				if ( $action == $actions["view"] )
				while ( $rec = $request->getRecordNext() )
					echo intval($rec["birth"])." ";
				else
				{
					// existing
					while ( $rec = $request->getRecordNext() )
						echo '<input type="checkbox" name="delchild[]" value="'.intval($rec["id"]).'" /> '.intval($rec["birth"])." ";
					
					// new
					echo '<input type="text" name="child[]" value="" onchange="javascript: '."if(this.value){elt = this.cloneNode(true); elt.value=''; this.parentNode.appendChild(elt); elt.focus(); }".'" />';
				}
			?>
			</p>
		</div>
	</div>
	<?php	} ?>
	<div class="more jqslide">
		<p class="titre">Groupes statiques</p>
		<div class="clip">
		<ul><?php
		if ( $action != $actions["add"] )
		{
			$query	= "(SELECT groupe.id, groupe.nom, (SELECT name FROM account WHERE groupe.createur = id) AS createur, false AS pro, NULL AS fct
				    FROM groupe_personnes, groupe
				    WHERE groupe.id = groupid
				      AND included
				      AND personneid = ".$id.")
				   UNION
				   (SELECT groupe.id, groupe.nom, (SELECT name FROM account WHERE groupe.createur = id) AS createur, true AS pro, fonctionid AS fct
				    FROM groupe_fonctions, groupe, personne_properso
				    WHERE groupe.id = groupid
				      AND included
				      AND personne_properso.id = ".$id."
				      AND fonctionid = personne_properso.fctorgid)
				   ORDER BY createur, nom";
			$request = new bdRequest($bd,$query);
			while ( $rec = $request->getRecordNext() )
			{
				echo '<li>';
				if ( $action == $actions["edit"] )
				echo '<input type="checkbox" name="grpdel['.($rec["pro"] == "t" ? 'fct' : 'id').']['.($rec["pro"] == "t" ? intval($rec["fct"]) : $id).']" value="'.intval($rec["id"]).'" title="retirer le contact de ce groupe" />';
				echo htmlsecure($rec["createur"] ? $rec["createur"] : "--").': ';
				echo '<a href="ann/search.php?grpid='.intval($rec["id"]).'&grpname='.urlencode($rec["nom"]).'">';
				echo htmlsecure($rec["nom"]);
				echo '</a>';
				if ( $rec["pro"] == 't' ) echo ' <span class="mini">(pro)</span>';
				echo '</li>';
			}
			$request->free();
		} // if ($action != $actions["add"])
		?></ul>
		<?php
			// ajout du contact dans un groupe static arbitraire
			if ( $action != $actions["view"] )
			{
				echo '<p class="addgrp">';
				$query  = " SELECT (SELECT name FROM account WHERE id = groupe.createur) AS createur, createur AS createurid,
					           groupe.id, groupe.nom, (createur = ".$user->getId()." OR createur IS NULL) AS perso
					    FROM groupe
					    WHERE groupe.id NOT IN (SELECT groupid FROM groupe_andreq)
					    ORDER BY perso DESC, createur, nom";
				$request = new bdRequest($bd,$query);
				if ( $request->countRecords() > 0 )
				{
					echo '<select name="grpstat[id]">';
					echo '<option value="">-groupes statiques-</option>';
					$lastid = -1;
					while ( $rec = $request->getRecordNext() )
					{
						if ( $lastid != intval($rec["createurid"]) )
						{
							if ( $lastid != -1 ) echo '</optgroup>';
							$lastid = intval($rec["createurid"]);
							echo '<optgroup label="'.htmlsecure($rec["createur"] ? $rec["createur"] : $default["commongrp"]).'">';
						}
						echo '<option value="'.intval($rec["id"]).'">'.htmlsecure($rec["nom"]).'</option>';
					}
					if ( $lastid != -1 ) echo '</optgroup>';
					echo '</select>';
					
					echo '<select name="grpstat[fct]">';
					echo '<option value="">-pas de fonction (perso)-</option>';
					if ( is_array($properso) )
					foreach ( $properso as $fctid => $values )
						echo '<option value="'.intval($fctid).'">'.htmlsecure($values["orgnom"].' - '.$values["fonction"]).'</option>';
					echo '</select>';
				}
			}	
			$request->free();
			echo "</p>";
		?>
		</div>
	</div>
	<?php
		// nom du fichier à ajouter
		$pagename = "ann-fiche.page.php";
		
		// a-t-on qqch à afficher ?
		$toprint = array();
		if ( count($config["mods"]) > 0 )
		{
			foreach ( $config["mods"] as $value )
			if ( is_readable($fn = $_SERVER["DOCUMENT_ROOT"].$config["website"]["root"].$value."/".$pagename) )
				$toprint[$value] = $fn;
		}
		
		// affichage de données des différents modules
		if ( count($toprint) > 0 )
		{
	?>
	<div id="more"></div>
	<div class="mod"><?php
		foreach ( $toprint as $key => $value )
		{
			$infos = array();
			include($value);
			foreach ( $infos as $info )
			{
				echo '<div class="'.htmlsecure($key).'">';
				echo '<p class="titre">'.$info["titre"].'</p>';
				echo '<div class="clip">'.$info["contenu"].'</div>';
				echo '</div>';
			}
		}
	?></div>
	<?php
		} // if ( count($toprint) > 0 )
		
		if ( $action == $actions["edit"] || $action == $actions["add"] )
			echo '<p class="valid"><input type="submit" name="valid" value="valider" /></p>';
	?>
</form>
</div>
<?php
	$titregen->free();
	$bd->free();
	includeLib("footer");
?>
