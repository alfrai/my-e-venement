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
	includeLib("actions");
	includeClass("bdRequest");
	
	$class = "pro";
	$action = $actions["edit"];
	$table = "modepaiement";
	
	includeLib("headers");
	
	// les suppressions
	if ( is_array($del = $_POST["del"]) )
	if ( !isset($_POST["secu"]) )
		$user->addAlert("Pour supprimer des enregistrements, il faut valider la case en bas du formulaire");
	else foreach ( $del as $value )
	if ( !$bd->delRecordsSimple($table,array("letter" => $value)) )
		$user->addAlert("Le mode de paiement ".intval($value)." n'a pu être effacé.");
	
	// les MAJ
	if ( is_array($upd = $_POST["upd"]) )
	foreach ( $upd as $letter => $content )
	if ( is_array($content) )
	{
		$arr = array();
		$arr[$name = "libelle"] = trim($content[$name]) ? trim($content[$name]) : NULL;
		$arr[$name = "letter"] = trim($content[$name]);
		
		$msg = "Le mode de paiement ".$arr["libelle"]." n'a pu être modifié.";
		if ( $arr["libelle"] != "" && $arr["letter"] )
		{
			if ( !$bd->updateRecordsSimple($table, array("letter" => $letter), $arr) )
				$user->addAlert($msg);
		}
	}
	
	// l'ajout
	if ( is_array($new = $_POST["new"]) )
	{
		$arr = array();
		$arr[$name = "libelle"] = trim($new[$name]) ? trim($new[$name]) : NULL;
		$arr[$name = "letter"] = trim($new[$name]) ? trim($new[$name]) : NULL;
		
		$msg = "La nouvelle entrée n'a pu être enregistrée";
		if ( $arr["libelle"] != "" && $arr["letter"] )
		if ( !$bd->addRecord($table,$arr) )
			$user->addAlert($msg);
	}
	
	// l'affichage
	$query	= " SELECT * FROM ".$table." ORDER BY libelle";
	$request = new bdRequest($bd,$query);
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a href="def/">Paramétrage</a><a class="nohref active">Modes de paiement</a><a href="." class="parent">..</a></p>
<div class="body">
<h2>Les modes de paiement</h2>
<form action="<?php $_SERVER["PHP_SELF"]; ?>" method="post" class="tarif"><ul><?php
	echo '<li class="th">';
	echo '<span class="del">suppr.</span>';
	echo '<span class="libelle">Description</span>';
	echo '<span class="cpt">Symbole</span>';
	echo '</li>';
	
	echo '<li class="new">';
	echo '<span class="del"></span>';
	echo '<span class="libelle"><input type="text" id="focus" value="" name="new[libelle]" /></span>';
	echo '<span class="cpt"><input type="text" value="" name="new[letter]" maxlength="1" size="1" /></span>';
	echo '</li>';
	
	while ( $rec = $request->getRecordNext() )
	{
		echo '<li class="upd">';
		echo '<span class="del"><input type="checkbox" name="del[]" value="'.htmlsecure($rec["letter"]).'" /></span>';
		echo '<span class="libelle"><input type="text" value="'.htmlsecure($rec["libelle"]).'" name="upd['.htmlsecure($rec["letter"]).'][libelle]" /></span>';
		echo '<span class="cpt"><input type="text" value="'.htmlsecure($rec["letter"]).'" name="upd['.htmlsecure($rec["letter"]).'][letter]" maxlength="1" size="1" /></span>';
		echo '</li>';
	}
?></ul>
<p class="submit"><input type="submit" name="submit" value="Valider" /></p>
<p class="secu onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0))">
	<input type="checkbox" name="secu" value="yes" onclick="javascript: ttt_spanCheckBox(this)" />
	Vous assumez complètement la suppression des moyens de paiement que vous avez cochés ainsi que sa conséquence !
</p>
</form>
</div>
<?php
	$request->free();
	includeLib("footer");
?>

