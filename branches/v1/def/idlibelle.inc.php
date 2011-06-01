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
	
	$action = $actions["edit"];
	
	includeLib("headers");
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
				
	// Ajout des nouvelles entrées
	$new = $_POST["new"];
	if ( is_array($new) )
	foreach ( $new as $key => $value )
	if ( $value["libelle"]["value"] && $value["libelle"]["value"] != $txtDefault )
	{
		$fields = array_keys($value);
		$arr = array();
		$arr["libelle"] = trim($value["libelle"]["value"]);
		if ( $buf = $fields[1] ) $arr[$buf] = trim($value[$buf]["value"]);
		if ( !$bd->addRecord($table,$arr) )
			$user->addAlert("Impossible d'ajouter l'enregistrement \"".$arr["libelle"]."\"");
	}
	
	// Modification des entrées
	$fct = $_POST["fct"];
	if ( is_array($fct) )
	foreach ( $fct as $key => $value )
	{
		$fields = array_keys($value);
		// Modifs pures
		if ( ( $value["origlibelle"] != $value["libelle"]["value"] || $fields[2] )
		  && $value["libelle"]["value"]
		  && $value["libelle"]["value"] != $txtDefault
		  && !isset($value["del"]) )
		{
			$arr = array();
			$arr["libelle"] = trim($value["libelle"]["value"]);
			if ( $buf = $fields[2] ) $arr[$buf] = trim($value[$buf]["value"]) || intval($value[$buf]["value"])."" == $value[$buf]["value"] ? trim($value[$buf]["value"]) : NULL;
			$bd->updateRecordsSimple($table,array("id" => intval($key)), $arr);
		}
		// Suppressions
		elseif ( isset($value["del"]) && intval($key) > 0 )
			$bd->delRecordsSimple($table,array("id" => intval($key)));
	} // foreach ( $fct as $key => $value )
	
	$query	= " SELECT * FROM ".$table." ORDER BY libelle";
	$request = new bdRequest($bd,$query);
	
	$fields = array_keys($request->getFields());
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a href="def/">Paramétrage</a><a class="nohref active"><?php echo htmlsecure($onglet) ?></a><a href="." class="parent">..</a></p>
<div class="body">
<h2><?php echo $texte ?></h2>
<form name="formu" class="param" action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
	<input type="hidden" name="txtDefault" value="<?php echo htmlsecure($txtDefault); ?>" />
	<p class="new" name="newpara">
		<span>&nbsp;</span>
		<span><?php printField("new[0][libelle]",NULL,addslashes(xmlsecure($txtDefault)),127,NULL,NULL,NULL,NULL,NULL,'id="focus"'); ?></span>
		<?php if ( $buf = $fields[2] ) { ?><span class="num"><?php printField("new[0][".htmlsecure($buf)."]",NULL,$fields[2],30); ?></span><?php } ?>
	</p>
	<?php
		while ( $rec = $request->getRecordNext() )
		{
	?>
	<p class="exist">
		<span>
			<?php echo intval($rec["id"]) ?>
			<input type="hidden" name="fct[<?php echo intval($rec["id"]) ?>][origlibelle]" value="<?php echo htmlsecure($rec["libelle"]) ?>" />
		</span>
		<span><?php printField("fct[".$rec["id"]."][libelle]",$rec["libelle"],addslashes(xmlsecure($txtDefault)),127); ?></span>
		<?php if ( $buf = $fields[2] ) { ?><span class="num"><?php printField("fct[".$rec["id"]."][".htmlsecure($buf)."]",$rec[$buf],$fields[2],30); ?></span><?php } ?>
		<span onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
			<input onclick="javascript: ttt_spanCheckBox(this);" type="checkbox" id="del" name="fct[<?php echo intval($rec["id"]) ?>][del]" value="true" /> supprimer
		</span>
	</p>
	<?php
		}
		$request->free();
	?>
	<p><span><input type="submit" name="submit" value="valider" /></span></p>
</form>
</div>
<?php
	$bd->free();
	includeLib("footer");
?>
