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
	
	// vérif d'usage
	if ( isset($_POST["new"]) )
	{
		if ( $_POST["new"] == $_POST["renew"] )
		{
			if ( $bd->updateRecordsRaw("account","id = ".$user->getId(),array("email" => $_POST["new"] ? "'".pg_escape_string($_POST["new"])."'" : "NULL")) > 0 )
				$user->addAlert("email correctement mis à jour.");
			else	$user->addAlert("email inchangé, erreur interne.");
		}
		else	$user->addAlert("Veillez à entrer deux fois la même adresse email.");
	}
	
	includeLib("headers");
?>
<h1>e-venement&nbsp;: Changer son mot de passe</h1>
<?php includeLib("tree-view"); ?>
<p class="actions">
	<a href="" class="active">Accueil</a><a class="add" href="ann/">Contacts</a><a href="org/">Organismes</a><?php
		if ( is_array($config["mods"]) )
		foreach ( $config["mods"] as $value )
		if ( is_dir($_SERVER["DOCUMENT_ROOT"].$config["website"]["root"]."/".$value) )
			includePage($value."/action.index");	
		
		if ( $user->hasRight($config["right"]["param"]) )
			echo '<a href="def/" class="add">'."Paramétrage".'</a>';
	?>
</p>
<div class="body">
<h2>Changez votre email</h2>
<p>Attention, l'adresse email saisie vous sera essentielle en cas de perte de mot de passe !! Veillez à donner une adresse valide.</p>
<form class="change email" action="<?php echo htmlsecure($_SERVER["PHP_SELF"]) ?>" method="post">
	<p class="email"><span>Saisir votre nouvel email:</span><span><input type="text" name="new" value="<?php echo htmlsecure($_POST["new"]); ?>" id="focus" /></span></p>
	<p class="reemail"><span>Saisir à nouveau votre email:</span><span><input type="text" name="renew" value="<?php echo htmlsecure($_POST["renew"]); ?>" /></span></p>
	<p class="valid"><span></span><span><input type="submit" name="submit" value="valider" /></span></p>
</form>
</div>
<?php
	includeLib("footer");
	$bd->free();
?>
