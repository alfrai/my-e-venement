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
	require("config.php");
	$css = "styles/main.css";
	$title = "e-venement : accueil";

	includeClass("navigation");
	includeClass("bd");
	includeClass("user");
	
	$level	= $config["right"]["view"];
	
	$nav	= new navigation();
        $user	= &$_SESSION["user"];
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	includeLib("login-check");
	includeLib("headers");
	$arr = parse_url($_SERVER["HTTP_REFERER"]);
	if ( strpos($arr["path"],"login.php") != FALSE )
		$user->addAlert('Vous êtes maintenant authentifié(e). Félicitations.');
?>
<h1>e-venement&nbsp;: <?php echo $user->getUserName(); ?>, bienvenue</h1>
<?php includeLib("tree-view"); ?>
<p class="actions">
	<a href="<?php echo $config["website"]["base"] ?>" class="<?php if ( !isset($_GET["sql"]) ) echo 'active' ?>">Accueil</a><a class="add" href="ann/">Contacts</a><a href="org/">Organismes</a><?php
		
		if ( $user->hasRight($config["right"]["devel"]) )
			echo '<a href="?sql" '.(isset($_GET["sql"]) ? 'class="active"' : '').'>Requête</a>';
		
		if ( is_array($config["mods"]) )
		foreach ( $config["mods"] as $value )
		if ( is_dir($_SERVER["DOCUMENT_ROOT"].$config["website"]["root"]."/".$value) )
			includePage($value."/action.index");	
		
		if ( $user->hasRight($config["right"]["param"]) )
			echo '<a href="def/" class="add">'."Paramétrage".'</a>';
	?><a href="<?php echo $config["divers"]["docs"] ?>" target="_blank">Docs</a>
</p>
<div class="body">
<?php
	if ( isset($_GET["sql"]) && $user->hasRight($config["right"]["devel"]) )
		includePage("ann/request");
	else
	{
?>
<h2>Bienvenue dans e-venement.</h2>
<p><?php @include("desc.txt"); ?></p>
<hr />
<h2>Actions simples de paramétrage accessibles à tous&nbsp;:</h2>
<ul>
	<li><a href="changepwd.php">Changer de mot de passe...</a></li>
	<li><a href="changeemail.php">Changer son adresse email...</a></li>
</ul>
<?php
	if ( is_dir($dir = $_SERVER["DOCUMENT_ROOT"].$config["website"]["root"].$config["website"]["dirtopast"]) )
	{
		$archives = scandir($dir);
		foreach ( $archives as $key => $value )
		if ( !is_dir($dir.'/'.$value) || $value[0] == '.' )
			unset($archives[$key]);
	}
	else	$archives = array();
	
	if ( count($archives) > 0 && !$config["website"]["old"] && $user->hasRight($config["right"]["archives"]) )
	{
?>
<hr />
<h2>Accès aux archives</h2>
<form class="past" action="topast.php" method="get">
	<p>
		<select name="past" onchange="javascript: this.form.submit();">
			<option value="" selected="selected"></option>
			<?php
				foreach ( $archives as $value )
					echo '<option value="'.$value.'">'.$value.'</option>';
			?>
		</select>
	</p>
	<p>
		<?php
			$code = "";
			$pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
			for ( $i=0 ; $i < 4 ; $i++ ) $code .= $pattern{rand(0,35)};
			$_SESSION["code"] = md5($code);
		?>
		Notez ce code, il vous sera demandé juste après: <input type="text" value="<?php echo htmlsecure($code) ?>" disabled="disabled" />
	</p>
</form>
<?php	} } ?>
</div>
<?php
	includeLib("footer");
	$bd->free();
?>
