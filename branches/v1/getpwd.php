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
	$title = "e-venement : login";
	includeJS("login");
	includeLib("headers");
?>
<h1>Bienvenue dans <i>e-venement</i></h1>
<?php includeLib("tree-view"); ?>
<p class="actions">
	<a class="nohref active">Login</a>
</p>
<div class="body">
<p>Vous avez perdu votre mot de passe ?</p>
<p>Entrez votre nom d'utilisateur et, si vous avez une adresse email de paramétrée, un nouveau mot de passe vous y sera envoyé.</p>
<form class="ident" action="<?php echo htmlsecure($config["website"]["base"]) ?>login.php?url=<?php echo urlencode($_GET["url"]) ?>" method="post" name="formu">
	<span class="login">Login&nbsp;: <input type="text" name="login" value="" /></span>
	<span class="valid"><input type="submit" name="valid" value="ok" /></span>
</form>
</div>
<?php
	includeLib("footer");
?>
