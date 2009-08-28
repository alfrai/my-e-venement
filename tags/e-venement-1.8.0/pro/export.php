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
	includeClass("bd/group");
	includeClass("bdRequest");
	
	$urls["base"] = "pro";
	$urls["fiche"] = "pro.php";
	
	$action = $actions["edit"];
	
	// valeurs par défaut (la clé du tableau doit etre la même que la clé du tableau passé en POST)
	$default["nom"] = "-DUPORT-";
	
	// récup des dates concernées pour les pros
	$query	= " SELECT * FROM params WHERE name = 'datemin' OR name = 'datemax'";
	$request = new bdRequest($bd,$query);
	$date = array();
	while ( $rec = $request->getRecordNext() )
	{
		if ( $rec["name"] == "datemin" )
			$date["min"] = $rec["value"];
		elseif ( $rec["name"] == "datemax" )
			$date["max"] = $rec["value"];
	}
	$request->free();
	
	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
	<h2>Exports / Extractions</h2>
	<form name="formu" action="" method="post">
		<p>Par date de présence&nbsp;: <select name="day"><?php
			echo '<option value="">-les journées-</option>';
			
			$timemax = strtotime($date["max"]);
			for ( $i = 0 ; ($time = strtotime($date["min"].' + '.$i.' days')) <= $timemax ; $i++ )
				echo '<option '.( $_POST["day"] == date("Y/m/d",$time) ? 'selected="selected"' : '' ).'>'.date("Y/m/d",$time).'</option>';
		?></select></p>
		<p>Sur l'ensemble de la période&nbsp;: <input type="checkbox" name="alldays" value="" <?php if ( isset($_POST["alldays"]) ) echo 'checked="checked"' ?> /></p>
		<p class="valid">
			<input type="submit" name="view" value="Voir" />
			<input type="submit" name="export" value="Exporter" />
			<span class="desc">
				Permet d'exporter le résultat vers un groupe personnel statique.
				Il est ensuite possible de faire des traitements sur ce groupe
				(extractions, croisements d'informations, ...) à partir de l'outil
				de recherche et d'extractions de l'annuaire de contacts.
			</span>
		</p>
	</form>
	<?php
		if( $_POST["day"] != "" || isset($_POST["alldays"]) )
		{
			// gestion des dates
			if ( !isset($_POST["alldays"]) )
			{
				$date["min"] = date("Y-m-d",strtotime($_POST["day"]));
				$date["max"] = date("Y-m-d",strtotime($_POST["day"].' + 1 day'));
			}
			else	$date["max"] = date("Y-m-d",strtotime($date["max"].' + 1 day'));
			$grpname = "pros présents du ".$date["min"]." au ".$date["max"];
			$grpid = 0;
	
			// l'export vers un groupe static
			if ( isset($_POST["export"]) && $user->hasRight($config["right"]["group"]) )
			{
				if ( $grpid = $bd->createGroup($grpname,$user->getId(),"Exporté le ".date($config["format"]["date"]." à ".$config["format"]["ltltime"])) )
					$user->addAlert("Un groupe statique personnel a été créé sous le nom de ".$grpname);
				else	$user->addAlert("Une erreur est survenue lors de la création ou la mise à jour de votre groupe");
			}
	?>
	<div class="view">
		<h2>
			Professionnels présents sur la période allant du
			<?php echo htmlsecure(date($config["format"]["date"],strtotime($date["min"])).' au '.date($config["format"]["date"],strtotime($date["max"])).' (exclu)') ?>
		</h2>
		<?php
			$query	= " SELECT DISTINCT personne.id, personne.nom, personne.prenom,
				                    personne.fctorgid, orgnom, orgid, fcttype, fctdesc
				    FROM manifestation AS manif, roadmap, personne_properso AS personne
				    WHERE manif.date >= '".pg_escape_string($date["min"])."'::date
				      AND manif.date <  '".pg_escape_string($date["max"])."'::date
				      AND roadmap.fctorgid = personne.fctorgid
				      AND roadmap.manifid = manif.id
				    ORDER BY nom, prenom, orgnom";
			$request = new bdRequest($bd,$query);
			
			echo '<ul class="manifs">';
			while ( $rec = $request->getRecordNext() )
			{
				if ( $grpid > 0 ) $bd->addGroupPro($grpid, intval($rec["fctorgid"]));
				echo '<li>';
				echo '<span><a href="ann/fiche.php?id='.intval($rec["id"]).'" class="pers '.($rec["npai"] == 't' ? 'npai' : '').'">';
				echo htmlsecure($rec["nom"].' '.$rec["prenom"]).'</a></span>';
				echo '<span><a href="pro/pro.php?fctorgid='.intval($rec["fctorgid"]).'" class="manifpro"><span>fiche pro</span></a></span>';
				echo '<span>(<a href="org/fiche.php?id='.intval($rec["orgid"]).'" class="org">'.htmlsecure($rec["orgnom"]).'</a>';
				echo ' - '.htmlsecure($rec["fctdesc"] ? $rec["fctdesc"] : $rec["fcttype"]).')</span>';
				echo '</li>';
			}
			echo '</ul>';
			echo '<p class="total">'.$request->countRecords().' personnes présentes</p>';
			
			$request->free();
		?>
		</div>
		<?php
			if ( $grpid > 0 )
			{
				echo '<p class="grpexp">';
				// lien vers un groupe si déjà existant
				echo '<span>Voir le groupe</span> ';
				echo '<span>&laquo;&nbsp;<a href="ann/search.php?grpid='.$grpid.'&grpname='.urlencode($grpname).'">'.htmlsecure($grpname)."</a>&nbsp;&raquo;</span>";
				echo '</p>';
			}
		?>
		<?php } // fin de l'affichage du résultat en cas de date sel. ?>
	</div>
<?php
	$bd->free();
	includeLib("footer");
?>
