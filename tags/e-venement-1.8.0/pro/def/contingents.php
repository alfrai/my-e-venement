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
	includeJS("ajax");
	includeJS("annu");
	
	$class = "pro param";
	$onglet = "Contingents";
	$titre = 'Paramétrage des personnes dont les contingents sont à prendre en compte.';
	
	includeLib("headers");
	
	// le formulaire a été soumis
	if ( isset($_POST["valid"]) || isset($_POST["client"]) )
	{
		// il y a un  nouvel enregistrement
		if ( intval($new = substr($_POST["client"],5)) > 0 )
		{
			$arr["fctorgid"] = intval($new);
			if ( !$bd->addRecord("contingentspro",$arr) )
			{
				$user->addAlert("Impossible d'ajouter votre paramètre.");
				$user->addAlert("Peut-être celui-ci existe déjà en base (vérifier dans la liste) ?");
			}
		}
		
		// suppressions
		$ok = true;
		if ( is_array($_POST["del"]) )
		foreach ( $_POST["del"] as $fctorgid )
			$ok = $ok && $bd->delRecordsSimple("contingentspro",array("fctorgid" => $fctorgid));
		if ( !$ok ) $user->addAlert("Impossible de supprimer au moins une de vos entrées.");
	}
	
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a href="pro/def/">Paramétrage</a><a class="nohref active"><?php echo htmlsecure($onglet) ?></a><a href="." class="parent">..</a></p>
<div class="body">
<h2><?php echo htmlsecure($titre) ?></h2>
<form name="formu" method="post" action="<?php echo $_SERVER["PHP_SELF"] ?>">
	<p class="new"><span class="user">
		Entrez le début du nom de la personne (pro) que vous recherchez&nbsp;:
		<input id="focus" type="text" name="new[name]" value="" onkeyup="javascript: annu_bill(this,true,true,true);" />
		(attendre la réponse)
	</span></p>
	<fieldset class="hidden">
		<input type="hidden" id="desc" name="desc" value="<?php
			echo $clic = htmlsecure("Pour plus d'aisance, cliquer sur ce lien de manière à l'ouvrir dans un nouvel onglet... (ctrl+clic)")
		?>" />
	</fieldset>
	<ul id="personnes"></ul>
	<hr/>
	<?php
		$query	= " SELECT personne.*
			    FROM contingentspro, personne_properso AS personne
			    WHERE contingentspro.fctorgid = personne.fctorgid
			    ORDER BY nom, prenom";
		$request = new bdRequest($bd,$query);
		
		while ( $rec = $request->getRecordNext() )
		{
	?>
	<p class="old">
		<span class="del"><input type="checkbox" name="del[]" value="<?php echo intval($rec["fctorgid"]) ?>" /></span><span class="desc">Supprimer cette personne</span>
		<span class="nom">
			<a href="ann/fiche.php?id=<?php echo intval($rec["id"]) ?>"><?php echo htmlsecure($rec["nom"].' '.$rec["prenom"]) ?></a>
			<span class="desc"><?php echo $clic ?></span>
		</span>
		<span class="org">(
			<a href="org/fiche.php?id=<?php echo intval($rec["orgid"]) ?>"><?php echo htmlsecure($rec["orgnom"]) ?></a>
			<span class="desc"><?php echo $clic ?></span>
			- <?php echo htmlsecure($rec["fctdesc"] ? $rec["fctdesc"] : $rec["fcttype"]) ?>
		)</span>
	</p>
	<?php	} ?>
	<p class="valid"><input type="submit" name="valid" value="Valider" /></p>
</form>
</div>
<?php
	$bd->free();
	includeLib("footer");
?>
