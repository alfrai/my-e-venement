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
	$subtitle = "Archivage de la billetterie et des modules connexes";
	$level = $config["right"]["devel"];
	require("conf.inc.php");
	includeClass("bdRequest");
	includeLib("headers");

	$bd     = new bd (      $config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	// répertoire d'archivage
	$dir = $_SERVER["DOCUMENT_ROOT"].$config["website"]["root"].$config["website"]["dirtopast"];
	
	$ok = true;
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a href="def/">Paramétrage</a><a class="active nohref">Archivage</a><a href="." class="parent">..</a></p>
<div class="body">
	<h2><?php echo htmlsecure($subtitle) ?></h2>
<?php
	// on vérifie que le nom de la base de donnée est bien libre
	$dbname = $config["database"]["name"].'-'.$_POST["archive"];
	$query = " SELECT count(*) > 0 AS ok FROM pg_database WHERE datname = '".$dbname."'";
	$request = new bdRequest($bd,$query);
	$dbexists = $request->getRecord("ok") == 't';
	$request->free();
	
	if ( is_writable($dir) && !$dbexists )
	{
		// on archive
		if ( $_POST["archive"] )
		if ( !is_dir($arch = $dir.'/'.$_POST["archive"]) )
		{
			// création du répertoire de l'archive
			mkdir($arch);
			
			// copie dans base d'archivage
			$query	= 'CREATE DATABASE "'.$dbname.'" TEMPLATE "'.$config["database"]["name"].'"';
			$request = new bdRequest($bd,$query);
			$request->free();
			
			$bd->beginTransaction();
			if ( $request === false )
			{
				$ok = false;
				$user->addAlert("Impossible de créer la base de données d'archivage");
			}
			
			
			// cleanage de la base courante des op de billetterie
			if ( in_array("evt",$config["mods"]) )
			{
				$oldpath = $bd->getPath();
				$bd->setPath("billeterie,public");
				
				// sauvegarde d'une image des données
				$query	= " SELECT DISTINCT personne.id AS personneid, personne.fctorgid AS fctorgid,
					           evt.nom AS evenement, manif.date
					    FROM personne_properso AS personne, evenement AS evt, manifestation AS manif,
					         reservation_pre AS preresa, reservation_cur AS resa, transaction
					    WHERE personne.id = transaction.personneid
					      AND ( personne.fctorgid = transaction.fctorgid
					         OR personne.fctorgid IS NULL AND transaction.fctorgid IS NULL )
					      AND preresa.transaction = transaction.id
					      AND preresa.id = resa.resa_preid
					      AND NOT resa.canceled
					      AND preresa.manifid = manif.id
					      AND manif.evtid = evt.id";
				$fields = array("personneid","fctorgid","evenement","date");
				$nbback = $bd->addRecordsQuery("personne_evtbackup",$fields,$query);
				
				if ( $nbback === false )
				{
					$ok = false;
					$user->addAlert("Impossible d'archiver les données de la billetterie.");
				}
				
				// suppression des données out of date
				// A FAIRE : ajouter les tables des manifestations et autres tarifs
				$arr = array();
				
				if ( in_array("sco",$config["mods"]) )		// module "sco"
				{
					$arr[] = "sco.tableau_manif";
					$arr[] = "sco.tableau_personne";
        }
        
				$arr[] = "reservation_cur";
				$arr[] = "reservation_pre";
				$arr[] = "reservation";
				$arr[] = "masstickets";
				$arr[] = "contingeant";
				$arr[] = "facture";
				$arr[] = "bdc";
				$arr[] = "paiement";
				$arr[] = "transaction";
				$arr[] = "evenement";
				
				foreach ( $arr as $value )
				if ( $bd->delRecordsSimple($value) === false )
				{
					$ok = false;
					$user->addAlert('Impossible de vider la table "'.$value.'".');
				}
				
				if( !$ok ) $user->addAlert("Impossible de nettoyer les données courantes !");
			}
			
			$bd->endTransaction($ok);
			$bd->setPath($oldpath);
			
			if ( $ok )
			{
				echo "<p>".htmlsecure("Nettoyage effectué. ".$nbback." enregistrements archivés.")."</p>";
	
				// configuration spéciale
				// faire une vérif pour injection de code PHP
				$strconf = '<?php
						$config["website"]["old"] = "'.$_POST["archive"].'";
						$config["website"]["root"] = "'.$config["website"]["root"].$config["website"]["dirtopast"].'/'.$_POST["archive"].'/";
						$config["website"]["base"] = "'.$config["website"]["base"].$config["website"]["dirtopast"].'/'.$_POST["archive"].'/";
						$config["website"]["libs"] = $_SERVER["DOCUMENT_ROOT"].$config["website"]["root"]."libs/";
						
						$config["database"]["name"] = "'.$dbname.'";
					?>';
				file_put_contents($arch."/config.archive.php",$strconf);
				
				// copie de fichiers
				$nocopy = array('.','..','past');
				$arr = scandir($root = $_SERVER["DOCUMENT_ROOT"].$config["website"]["root"]);
				foreach ( $arr as $value )
				if ( !in_array($value,$nocopy) )
					exec('cp -r -L "'.$root.$value.'" "'.$arch.'/'.$value.'"');
			
				// message "ok"
				echo "<p>".htmlsecure("Archivage effectué")."</p>";
			}
			else	echo "<p>".htmlsecure("Erreur dans l'archivage en base de données.")."</p>";
		}
		else	echo "<p>".htmlsecure("Impossible d'archiver au nom de \"".htmlsecure($_POST["archive"])."\", archive déjà existante.")."</p>";
?>
	<form action="<?php htmlsecure($_SERVER["PHP_SELF"]) ?>" method="post">
		<p>Nom de l'archive:&nbsp;<input type="text" name="archive" value="" /></p>
		<p><input type="submit" name="submit" value="Archiver" /></p>
	</form>
<?php	} else { ?>
	<p>Impossible de procéder à l'archivage de vos données, vérifier&nbsp;</p>
	<ul>
		<li>les droits du répertoire '<?php echo htmlsecure($dir); ?>'</li>
		<li>l'absence d'existence ou l'existance préalable d'une base au nom de '<?php echo htmlsecure($dbname) ?>'</li>
	</ul>
<?php	} ?>
	<div>
		<h3>Archives actuelles</h3>
		<ul class="archives">
		<?php
			$arr = scandir($dir);
			foreach ( $arr as $value )
			if ( $value[0] != '.' )
				echo '<li>'.htmlsecure($value).'</li>';
		?>
		</ul>
	</div>
</div>
<?php
	includeLib("footer");
	$bd->free();
?>
