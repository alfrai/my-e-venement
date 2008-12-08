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
	includeClass("user");
	session_start();
	
	$css = "styles/main.css";
	$title = "e-venement : login";
	includeJS("login");
	includeLib("getpwd");
	includeLib("headers");
	
	// suppression de la variable de session user si déjà loggué
	if ( $_SESSION["user"] )
	{
		echo '<script type="text/javascript">';
		echo "alert('".$_SESSION["user"]->getAlert()."')";
		echo '</script>'; 
		unset($_SESSION["user"]);
	}
?>
<h1>Bienvenue dans <i>e-venement</i></h1>
<?php includeLib("tree-view"); ?>
<p class="actions">
	<a class="nohref active">Login</a>
</p>
<div class="body">
<p>Toute utilisation nécessite d'être authentifié</p>
<?php
	if ( isset($_POST["login"]) )
	{
		// création de l'objet $bd
		$bd     = new bd (      $config["database"]["name"],
					$config["database"]["server"],
					$config["database"]["port"],
					$config["database"]["user"],
					$config["database"]["passwd"] );
		
		// récup de l'email
		$email = getEmail($bd,$_POST["login"]);
		
		// génération, update de la base et envoi d'un email d'info
		$ok = false;
		if ( $email )
		{
			$ok = true;
			$pwd = getNewPasswd();
			$bd->beginTransaction();
			if ( $ok = $ok && $bd->updateRecordsSimple('account',array('login' => $_POST["login"]),array('password' => md5($pwd))) )
				$ok = $ok && sendPasswd($email,$_POST["login"],$pwd);
			$bd->endTransaction($ok);
		}
		
		// message d'information
		echo '<p class="getpwd">';
		if ( $ok )
			echo 'Mot de passe mis à jour et transféré par email.';
		else	echo 'Impossible de mettre le mot de passe à jour.';
		echo '</p>';
		
		$bd->free();
	}
	
	// si pas de code et année passée, on dégage
	if ( !$config["website"]["old"] || $config["website"]["old"] && isset($_SESSION["code"]) )
	{
?>
<form class="ident" action="<?php echo htmlsecure($config["website"]["base"]) ?>validation.php?url=<?php echo urlencode($_GET["url"]) ?>" method="post" name="formu">
	<span class="login">Login&nbsp;: <input type="text" name="login" value="" /></span>
	<span class="passwd">Mot de passe&nbsp;: <input type="password" name="passwd" value="" /></span>
	<?php if ( $config["website"]["old"] ) { ?><span class="past">Code à fournir: <input type="text" name="code" value="" /></span><?php } ?>
	<span class="valid"><input type="submit" name="valid" value="ok" /></span>
	<span class="lostpwd"><a href="<?php echo htmlsecure('getpwd.php?url=').urlencode($_GET["url"]) ?>">Vous avez perdu votre mot de passe&nbsp;?</a></span>
</form>
<?php
	}
	else echo "Impossible d'accéder à la partie souhaitée";
?>
</div>
<?php
	includeLib("footer");
?>
