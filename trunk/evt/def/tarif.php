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
	
	$class = "accounts";
	$action = $actions["edit"];
	$table = "tarif";
	
	includeLib("headers");
?>
<pre>
<?php
	// les suppressions
	if ( is_array($del = $_POST["del"]) )
	if ( !isset($_POST["secu"]) ) $user->addAlert("Pour désactiver des enregistrements, il faut valider la case en bas du formulaire");
	else foreach ( $del as $value )
	{
		$bd->beginTransaction();
		$query = " SELECT description, key, prix, true AS desact FROM tarif WHERE id = ".intval($value);
		$request = new bdRequest($bd,$query);
		if ( !$bd->addRecord("tarif",$request->getRecord()) )
			$user->addAlert("L'enregistrement ".intval($value)." n'a pu être désactivé.");
		$request->free();
		$bd->endTransaction();
	}
	
	// l'ajout
	if ( is_array($new = $_POST["new"]) )
	{
    echo preg_match("/[\\ \+.\-_;!:?äÄöÖüÜß<>=\/'\"]/",$new['key']);
    if ( preg_match("/[\\ \+.\-_;!:?äÄöÖüÜß<>=\/'\"]/",$new['key']) > 0 )
	    $user->addAlert('Des caractères interdits ont été utilisés dans la clé du tarif...');
	  else
	  {
	    $arr = array();
		  $arr[$name = "description"] = trim($new[$name]) ? trim($new[$name]) : NULL;
		  $arr[$name = "key"] = strtoupper(trim(substr($new[$name],0,5)));
		  $arr[$name = "contingeant"] = $new[$name] == 'yes' ? "t" : "f";
		  $arr[$name = "prix"] = $arr["contingeant"] == "t" ? 0 : floatval($new[$name]);
		
		  $msg = "La nouvelle entrée n'a pu être enregistrée";
		  if ( $arr["key"] && $arr["description"] != "" )
		  if ( !$bd->addRecord($table,$arr) )
			  $user->addAlert($msg);
	  }
	}
	
	// l'affichage
	$query	= "(SELECT *, 1 AS classmt
		    FROM ".$table."
		    WHERE date IN (SELECT MAX(date) FROM ".$table." AS tmp WHERE key = tarif.key)
		      AND desact = false)
		   UNION
		   (SELECT *, 2 AS classmt
		    FROM ".$table."
		    WHERE date NOT IN (SELECT MAX(date) FROM ".$table." AS tmp WHERE key = tarif.key)
		       OR desact = true)
		   ORDER BY classmt, key, date DESC";
	$request = new bdRequest($bd,$query);
?>
</pre>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a href="def/">Paramétrage</a><a class="nohref active">Tarifs</a><a href="." class="parent">..</a></p>
<div class="body">
<h2>Les tarifs par défaut</h2>
<form action="<?php $_SERVER["PHP_SELF"]; ?>" method="post" class="tarif"><ul><?php
	echo '<li class="th">';
	echo '<span class="del">desact.</span>';
	echo '<span class="id"></span>';
	echo '<span class="key">Clé<sup>*</sup></span>';
	echo '<span class="desc">Description</span>';
	echo '<span class="prix">Prix<sup>*</sup></span>';
	echo '<span class="cont">Cont.</span>';
	echo '<span class="date">Date de valeur</span>';
	echo '</li>';
	
	echo '<li class="new">';
	echo '<span class="del"></span>';
	echo '<span class="id"></span>';
	echo '<span class="key"><input type="text" value="" maxlength="5" name="new[key]" id="focus" /></span>';
	echo '<span class="desc"><input type="text" value="" name="new[description]" /></span>';
	echo '<span class="prix"><input type="text" value="" name="new[prix]" /> €</span>';
	echo '<span class="cont"><input type="checkbox" name="new[contingeant]" value="yes"/></span>';
	echo '<span class="date"></span>';
	echo '</li>';
	
	while ( $rec = $request->getRecordNext() )
	{
		echo '<li class="'.($rec["classmt"] == 1 ? 'upd' : 'old').'">';
		echo '<span class="del">';
		echo $rec["classmt"] == 1
			? '<input type="checkbox" name="del[]" value="'.intval($rec["id"]).'"/>'
			: ($rec["desact"] == 't' ? 'x' : '-');
		echo '</span>';
		echo '<span class="id">'.intval($rec["id"]).'</span>';
		echo '<span class="key">'.htmlsecure($rec["key"]).'</span>';
		echo '<span class="desc">'.htmlsecure($rec["description"]).'</span>';
		echo '<span class="prix">'.floatval($rec["prix"]).' €</span>';
		echo '<span class="cont">'.($rec["contingeant"] == 't' ? 'x' : '-').'</span>';
		echo '<span class="date">'.htmlsecure($rec["date"]).'</span>';
		echo '</li>';
	}
?></ul>
<p class="submit"><input type="submit" name="submit" value="Valider" /></p>
<p class="secu onclick" onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0))">
	<input type="checkbox" name="secu" value="yes" onclick="javascript: ttt_spanCheckBox(this)" />
	Vous assumez complètement la désactivation des tarifs que vous avez cochés ainsi que leurs conséquences !
</p>
</form>
</div>
<?php
	$request->free();
	includeLib("footer");
?>

