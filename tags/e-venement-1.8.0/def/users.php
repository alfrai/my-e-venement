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
	includeJS("ttt");
	includeLib("ttt");
	includeLib("getpwd");
	includeLib("actions");
	includeClass("bdRequest");
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	$class = "accounts";
	$action = $actions["edit"];
	
	$default["expire"] = "-AAAA/MM/JJ-";
	$default["description"] = "-Description-";
	$default["password"] = "-xxxxxx-";
	$default["email"] = "-ilene@dom.tld-";
	
	includeLib("headers");

	// récupération des données
	$del = $_POST["del"];
	$mod = $_POST["field"];
	$new = $mod["new"];
	unset($mod["new"]);
	
	// ce qu'on va supprimer
	if ( is_array($del) )
	{
		foreach ( $del as $key => $value )
		if ( $value == "yes" )
		{
			$r = $bd->delRecordsSimple("account",array("id"=>intval($key)));
			$user->addAlert("L'utilisateur d'identifiant n°".intval($key)." ".($r ? "a bien été supprimé." : "n'a pas pu être supprimé"));
		}
	}
	
	// ce qu'on va modifier
	if ( is_array($mod) )
	{
		foreach ( $mod as $key => $value )
		{
			$arr = array();
			if ( is_array($value) )
			{
				foreach ( $value as $name => $pair )
					$arr[$name]	= $pair["value"] != $pair["default"] && $pair["value"]
							? trim($pair["value"])
							: NULL;
				if ( $arr["password"] == NULL )
					unset($arr["password"]);
				else	$arr["password"] = md5($arr["password"]);
				if ( $arr["email"] && $arr["sendemail"] == 't' )
					$arr["password"] = sendPasswd($arr["email"],$arr["login"]);
				unset($arr["sendemail"]);
				unset($arr["login"]);
				$arr["level"] = intval($arr["level"]);
			}
			if ( $arr["level"] <= $user->getLevel() )
			{
				if ( !$bd->updateRecordsSimple("account",array("id"=>intval($key)),$arr) )
					$user->addAlert("L'utilisateur ".$arr["name"]." n'a pas pu être mis à jour.");
			}
			else	$user->addAlert("Impossible d'augmenter le niveau de droits de ".$arr["name"]." au delà de vos propres droits.");
		}
	}
	
	// ce qu'on va créer
	if ( is_array($new) )
	{
		$arr = array();
		foreach ( $new as $name => $pair )
		{
			if ( $pair["value"] != $pair["default"] && $pair["value"] )
				$arr[$name] = trim($pair["value"]) ? trim($pair["value"]) : NULL;
		}
		if ( $arr["login"] && ( $arr["password"] || ($arr["sendemail"] == 't' && $arr["email"]) ) && $arr["name"] )
		{
			$arr["level"] = intval($arr["level"]);
			$arr["password"] = md5($arr["password"]);
			unset($arr["sendemail"]);
			if ( $arr["email"] && $arr["sendemail"] == 't' )
				$arr["password"] = sendPasswd($arr["email"],$arr["login"]);
			$r = $bd->addRecord("account",$arr);
			$user->addAlert("L'utilisateur ".$arr["name"]." ".($r ? "a bien été créé" : "n'a pas pu être créé"));
		}
	}
	
	// ce qu'on va afficher
	$query	= " SELECT * FROM account ORDER BY login";
	$request = new bdRequest($bd,$query);
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a href="def/">Paramétrage</a><a class="nohref active">Comptes</a><a href="." class="parent">..</a></p>
<div class="body">
<h2>Gestion des comptes</h2>
<form method="post" action="<?php echo $_SERVER["PHP_SELF"] ?>" class="accounts" name="formu">
	<ul class="user">
		<li class="desc">suppr.</li>
	<?php
		while ( $rec = $request->getRecordNext() )
		{
			echo '<li>';
			echo '<input type="checkbox" name="del['.intval($rec["id"]).']" value="yes" />&nbsp;'.intval($rec["id"]).'- ';
			echo '<a onclick="javascript: ttt_users(this);">'.htmlsecure($rec["login"]).'</a>';
			echo '<input type="hidden" name="field['.intval($rec["id"]).'][login][value]" value="'.htmlsecure($rec["login"]).'" />: ';
			printField("field[".intval($rec["id"])."][".($name = "name")."]",$rec[$name],$default[$name],255,15);
			echo '<ul class="nodisplay">';
				echo '<li>Description: ';
				printField("field[".intval($rec["id"])."][".($name = "description")."]",$rec[$name],$default[$name],NULL,NULL,true);
				echo '</li>';
				echo '<li>Mot de passe: ';
				echo '<input type="password" name="field['.intval($rec["id"]).'][password][value]" size="15" maxlength="255" class="exemple" value="-xxxxxx-" onfocus="javascript: ttt_onfocus(this,\'-xxxxxx-\')" onblur="javascript: ttt_onblur(this,\'-xxxxxx-\')" />';
				echo '<input type="hidden" name="field['.intval($rec["id"]).'][password][default]" value="-xxxxxx-" />';
				echo '</li>';
				if ( $rec["expire"] || $action != $actions["view"] )
				{
					echo '<li>Expire: ';
					printField("field[".intval($rec["id"])."][".($name = "expire")."]",$rec[$name],$default[$name],255,12);
					echo '</li>';
				}
				echo '<li class="active">Actif: ';
				if ( $action == $actions["view"] )
					htmlsecure($rec["active"] == 't' ? 'oui' : 'non').'</li>';
				else
				{
					echo '<span onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('."'input'".').item(0))" class="onclick"><input type="radio" onclick="javascript: ttt_spanCheckBox(this);" name="field['.intval($rec["id"]).'][active][value]" value="t" '.($rec["active"] != 'f' ? 'checked="checked"' : '' ).' /> oui</span>';
					echo '<span onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('."'input'".').item(0))" class="onclick"><input type="radio" onclick="javascript: ttt_spanCheckBox(this);" name="field['.intval($rec["id"]).'][active][value]" value="f" '.($rec["active"] == 'f' ? 'checked="checked"' : '' ).' /> non</span>';
					
				}
				echo '</li>';
				echo '<li>email: ';
				printField("field[".intval($rec["id"])."][".($name = "email")."]",$rec[$name],$default[$name],255,15);
				echo '</li>';
				// envoi par email du passwd
				if ( $action != $actions["view"] )
				{
					echo '<li class="bymail">Envoyer un mot de passe aléatoire par mail ?';
					echo '<span onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('."'input'".').item(0))" class="onclick"><input onclick="javascript: ttt_spanCheckBox(this)" type="radio" name="field['.intval($rec["id"]).'][sendemail][value]" value="t" /> oui</span>';
					echo '<span onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('."'input'".').item(0))" class="onclick"><input onclick="javascript: ttt_spanCheckBox(this)" type="radio" name="field['.intval($rec["id"]).'][sendemail][value]" value="f" checked="checked" /> non</span>';
					echo '</li>';
				}
				echo '<li class="level">Niveau de droits<sup>*</sup>&nbsp;: ';
				printField("field[".intval($rec["id"])."][".($name = "level")."]",intval($rec[$name]),0,24,5,false,NULL,NULL,true,intval($rec["level"]) > $user->getLevel() ? 'disabled="disabled"' : NULL);
				echo '</li>';
			echo '</ul>';
			echo '</li>';
		}
	?>
		<li class="new">
		<?php
			printField("field[new][".($name = "login")."]",$rec[$name],$default[$name],255,10,NULL,NULL,NULL,true,'id="focus"');
			echo ': ';
			printField("field[new][".($name = "name")."]",$rec[$name],$default[$name],255,15);
			echo '<ul>';
				echo '<li>Description: ';
				printField("field[new][".($name = "description")."]",$rec[$name],$default[$name],NULL,NULL,true);
				echo '</li>';
				echo '<li>Mot de passe: ';
				echo '<input type="password" name="field[new][password][value]" size="15" maxlength="255" class="exemple" value="-xxxxxx-" onfocus="javascript: ttt_onfocus(this,\'-xxxxxx-\')" onblur="javascript: ttt_onblur(this,\'-xxxxxx-\')" />';
				echo '</li>';
				if ( $rec["expire"] || $action != $actions["view"] )
				{
					echo '<li>Expire: ';
					printField("field[new][".($name = "expire")."]",$rec[$name],$default[$name],255,12);
					echo '</li>';
				}
				echo '<li class="active">Actif: ';
				if ( $action == $actions["view"] )
					htmlsecure($rec["active"] == 't' ? 'oui' : 'non').'</li>';
				else
				{
					echo '<span onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('."'input'".').item(0))" class="onclick"><input onclick="javascript: ttt_spanCheckBox(this)" type="radio" name="field[new][active]" value="t" '.($rec["active"] != 'f' ? 'checked="checked"' : '' ).' /> oui</span>';
					echo '<span onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('."'input'".').item(0))" class="onclick"><input onclick="javascript: ttt_spanCheckBox(this)" type="radio" name="field[new][active]" value="f" '.($rec["active"] == 'f' ? 'checked="checked"' : '' ).' /> non</span>';
				}
				echo '<li>email: ';
				printField("field[new][".($name = "email")."]","",$default[$name],255,15);
				echo '</li>';
				// envoi par email du passwd
				if ( $action != $actions["view"] )
				{
					echo '<li class="bymail">Envoyer un mot de passe aléatoire par mail ?';
					echo '<span onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('."'input'".').item(0))" class="onclick"><input onclick="javascript: ttt_spanCheckBox(this)" type="radio" name="field[new][sendemail][value]" value="t" /> oui</span>';
					echo '<span onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('."'input'".').item(0))" class="onclick"><input onclick="javascript: ttt_spanCheckBox(this)" type="radio" name="field[new][sendemail][value]" value="f" checked="checked" /> non</span>';
					echo '</li>';
				}
				echo '<li class="level">Niveau de droits<sup>*</sup>&nbsp;: ';
				printField("field[new][".($name = "level")."]",intval($rec[$name]),0,24,5);
				echo '</li>';
			echo '</ul>';
		?>
		</li>
	</ul>
	<p class="notes">
		<sup>*</sup> ici les droits valent&nbsp;:
		<ul>
			<li><?php echo intval($config["right"]["view"]) ?> - Consultation simple</li>
			<li><?php echo intval($config["right"]["group"]) ?> - Création/modification de groupes</li>
			<li><?php echo intval($config["right"]["add"]) ?> - Ajout de personnes ou d'organismes</li>
			<li><?php echo intval($config["right"]["edit"]) ?> - Modification de personnes ou d'organismes</li>
			<li><?php echo intval($config["right"]["del"]) ?> - Suppression de personnes ou d'organismes</li>
			<li><?php echo intval($config["right"]["commongrp"]) ?> - Suppression de groupes communs</li>
			<li><?php echo intval($config["right"]["archives"]) ?> - Accès aux archives</li>
			<li><?php echo intval($config["right"]["param"]) ?> - Paramétrage de l'application</li>
			<li><?php echo intval($config["right"]["devel"]) ?> - Maintenance de l'application</li>
		</ul>
	</p>
	<p class="submit"><input type="submit" name="submit" value="Valider" /></p>
</form>
</div>
<?php
	includeLib("footer");
?>

