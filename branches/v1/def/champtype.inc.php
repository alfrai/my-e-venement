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
	$class = "champtype";
	$champ = str;
	
	includeLib("headers");
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	if ( $_POST["newtype"] )
		$bd->addRecord($table, array("str" => $_POST["newtype"], "usage" => $usage));
	
	if ( is_array($_POST["del"]) )
	{
		$del = $_POST["del"];
		foreach ( $del as $key => $value )
			$del[$key] = "'".pg_escape_string($del[$key])."'";
		$bd->delRecords($table,"str IN (".implode(",",$del).") AND usage = '".$usage."'");
	}
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a href="def/">Paramétrage</a><a class="nohref active"><?php echo htmlsecure($onglet) ?></a><a href="." class="parent">..</a></p>
<div class="body">
<h2><?php echo htmlsecure($titre) ?></h2>
<form name="formu" method="post" action="<?php echo $_SERVER["PHP_SELF"] ?>">
	<p class="new">Nouvelle entrée générique&nbsp;:<br /><input type="text" name="newtype" id="focus" value="" /></p>
	<p class="desc">suppr.</p>
	<?php
		$query	= " SELECT str FROM ".$table." WHERE usage = '".$usage."' ORDER BY str";
		$request = new bdRequest($bd,$query);
		while ( $rec = $request->getRecordNext() )
			echo '<p class="old"><input type="checkbox" name="del[]" value="'.htmlsecure($rec[$champ]).'" /> '.htmlsecure($rec[$champ])." </p>";
		$request->free();
	?>
	<p class="valid"><input type="submit" name="submit" value="Valider" /></p>
</form>
</div>
<?php
	$bd->free();
	includeLib("footer");
?>
