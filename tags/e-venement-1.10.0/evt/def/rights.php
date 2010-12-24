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
	$class = "evt";
	$onglet = "Droits evenements";
	$titre = 'Gestion des droits de la partie évènements/billetterie.';
	
	// la condition de l'espace courant
	$spaceid = intval($_POST['spaceid']);
	$spacecond = $spaceid > 0 ? 'spaceid = '.$spaceid : 'spaceid IS NULL';
	
	includeLib("headers");
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	// le formulaire a été soumis
	if ( isset($_POST["submit"]) )
	{
		// il y a un  nouvel enregistrement
		if ( $_POST["new"]["level"] != "" && intval($_POST["new"]["accountid"]) )
		{
			$arr["id"] = intval($_POST["new"]["accountid"]);
			$arr["level"] = intval($_POST["new"]["level"]);
			if ( $spaceid > 0 )
  			$arr['spaceid'] = $spaceid;
			if ( !$bd->addRecord("rights",$arr) )
				$user->addAlert("Impossible d'ajouter votre sélection.");
		}
		
		// il y a des modifs à faire
		$ok = true;
		if ( is_array($_POST["level"]) )
		foreach ( $_POST["level"] as $id => $value )
			$ok = $ok && $bd->updateRecords("rights",'id = '.intval($id).' AND '.$spacecond,array("level" => intval($value)));
		if ( !$ok ) $user->addAlert("Impossible de mettre à jour au moins une de vos entrées.");
		
		// suppressions
		$ok = true;
		if ( is_array($_POST["del"]) )
		foreach ( $_POST["del"] as $id )
			$ok = $ok && $bd->delRecords("rights",'id = '.intval($id).' AND '.$spacecond);
		if ( !$ok ) $user->addAlert("Impossible de supprimer au moins une de vos entrées.");
	}
	
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a href="evt/def/">Paramétrage</a><a class="nohref active"><?php echo htmlsecure($onglet) ?></a><a href="." class="parent">..</a></p>
<div class="body">
<h2><?php echo htmlsecure($titre) ?></h2>
<?php if ( $config['evt']['spaces'] ): ?>
<form class="space" method="post" action="<?php echo $_SERVER["PHP_SELF"] ?>">
  <p>
    <?php
      $query =  ' SELECT *
                  FROM space
                '.(!$user->hasRight($config["right"]["param"]) ? 'WHERE id IN ( SELECT spaceid FROM rights WHERE level >= 10 AND id = '.$user->getId().' )' : '').'
                  ORDER BY name';
      $request = new bdRequest($bd,$query);
    ?>
    <select name="spaceid" onchange="javascript: submit()">
      <option value="">Espace par défaut</option>
      <?php while ( $rec = $request->getRecordNext() ): ?>
      <option value="<?php echo intval($rec['id']) ?>" <?php echo intval($rec['id']) == $spaceid ? 'selected="selected"' : '' ?>><?php echo htmlsecure($rec['name']) ?></option>
      <?php endwhile; ?>
    </select>
    <?php $request->free(); ?>
  </p>
</form>
<?php endif; ?>
<form name="formu" method="post" action="<?php echo $_SERVER["PHP_SELF"] ?>">
	<p class="new">
	  <input type="hidden" name="spaceid" value="<?php echo $spaceid ? $spaceid : $user->evtspace ?>" />
		<span class="user"><?php
			echo '<select name="new[accountid]">';
			echo '<option value="">-Les comptes-</option>';
			
			$query	= " SELECT *
				    FROM account
				    WHERE id NOT IN ( SELECT id FROM rights WHERE $spacecond )
				    ORDER BY name";
			$request = new bdRequest($bd,$query);
			
			while ( $rec = $request->getRecordNext() )
				echo '<option value="'.intval($rec["id"]).'">'.htmlsecure($rec["name"]).' ('.htmlsecure($rec["login"]).')</option>';
			
			$request->free();
			echo '</select>';
		?></span>
		<span class="level"><input type="text" name="new[level]" maxlength="3" size="3" /></span>
	</p>
	<?php
		$query	= " SELECT account.id, account.login, account.name, rights.level
			    FROM account, rights
			    WHERE rights.id = account.id AND $spacecond";
		$request = new bdRequest($bd,$query);
		
		while ( $rec = $request->getRecordNext() )
		{
	?>
	<p class="old">
		<span class="del"><input type="checkbox" name="del[]" value="<?php echo intval($rec["id"]) ?>" /></span><span class="desc">Retirer les droits de cette entrée</span>
		<span class="user"><?php echo htmlsecure($rec["name"].' ('.$rec["login"].')') ?></span>
		<span class="level"><input type="text" name="level[<?php echo intval($rec["id"]) ?>]" value="<?php echo intval($rec["level"]) ?>" maxlength="3" size="3" /></span>
	</p>
	<?php	} ?>
	<hr/>
	<p class="notes">
		<sup>*</sup> ici les droits valent&nbsp;:
		<ul>
			<li><?php echo intval($config["evt"]["right"]["view"]) ?> - Consultation simple (est donné d'office à tous les administrateurs généraux)</li>
			<li><?php echo intval($config["evt"]["right"]["simple"]) ?> - Ajout/modification/suppression, accès minimaliste</li>
			<li><?php echo intval($config["evt"]["right"]["mod"]) ?> - Ajout/modification/suppression</li>
			<li><?php echo intval($config["evt"]["right"]["unblock"]) ?> - Possibilité de débloquer des opérations de billetterie</li>
			<li><?php echo intval($config["evt"]["right"]["param"]) ?> - Paramétrage du module, pas de prise en compte des opérations bloquées, tous espaces confondus</li>
		</ul>
	</p>
	<p class="valid"><input type="submit" name="submit" value="Valider" /></p>
</form>
</div>
<?php
	$bd->free();
	includeLib("footer");
?>
