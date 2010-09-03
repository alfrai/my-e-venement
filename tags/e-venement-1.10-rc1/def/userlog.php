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
	$subtitle = "Liste des tentatives de connexion au service";
	require("conf.inc.php");
	includeClass("bdRequest");
	includeLib("headers");

	$bd     = new bd (      $config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a href="def/">Paramétrage</a><a class="active nohref">Userlog</a><a href="." class="parent">..</a></p>
<div class="body">
<h2><?php echo htmlsecure($subtitle) ?></h2>
<table border="1">
	<tr>
		<th>Date</td>
		<th>Compte utilisé</td>
		<th>Nom essayé</td>
		<th>Adresse IP</td>
		<th>Réussite ?</td>
	</tr>
	<?php
		$query	= " SELECT login.date, login.triedname, login.ipaddress, login.success, tmp.login
			    FROM login, (SELECT account.id, account.login FROM account UNION SELECT NULL AS id, NULL AS login) AS tmp 
			    WHERE tmp.id = login.accountid OR tmp.id IS NULL AND login.accountid IS NULL
			    ORDER BY date DESC LIMIT 25";
		if ( intval($_GET["r"]) > 0 )
		$query .= " OFFSET ".intval($_GET["r"]);
		
		$request = new bdRequest($bd,$query);
		while ( $rec = $request->getRecordNext() )
		{
	?>
	<tr>
		<td><?php echo htmlsecure(date($config["format"]["date"]." ".$config["format"]["time"],strtotime($rec["date"]))); ?></td>
		<td><?php echo htmlsecure($rec["login"]); ?></td>
		<td><?php echo htmlsecure($rec["triedname"]); ?></td>
		<td><?php echo htmlsecure($rec["ipaddress"]); ?></td>
		<td><?php echo htmlsecure($rec["success"] == "t" ? "oui" : "non"); ?></td>
	</tr>
	<?php
		}
		$request->free();
	?>
</table>
<div class="offset">
	<?php if ( intval($_GET["r"])-25 > -25 ) { ?><a href="def/userlog.php?r=<?php echo intval($_GET["r"]) - 25 < 0 ? "0" : intval($_GET["r"]) - 25; ?>">précédent</a><?php } ?>
	<a href="def/userlog.php?r=<?php echo intval($_GET["r"])+25; ?>">suivant</a>
</div>
</div>
<?php
	includeLib("footer");
	$bd->free();
?>
