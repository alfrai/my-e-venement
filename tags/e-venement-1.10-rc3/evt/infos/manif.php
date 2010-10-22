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
	require_once("conf.inc.php");
	includeClass("bdRequest");
	includeLib("ttt");
	includeLib("jauge");
	includeJS("ttt");
	includeJS("ajax");
	includeJS("annu");
	includeJS("jquery");
	includeLib("actions");
	
	$evt = true;
	$manifid = intval($_GET["id"]) > 0 ? intval($_GET["id"]) : intval($_POST["id"]);
	$id = intval($_GET["evtid"]);
	$class = "evt";
	$css[] = "evt/styles/colors.css.php";
	$css[] = "evt/styles/jauge.css";
	$cssprint = "evt/styles/print.css";
	
	$mod = $user->evtlevel >= $config["evt"]["right"]["mod"];
	
	includeLib("headers");
	
	// enregistrement des tarifs spéciaux
	if ( is_array($tarifs = $_POST["prix"]) && $mod )
	{
		foreach ( $tarifs as $tarifid => $prix )
		{
			$ok = $bd->delRecordsSimple("manifestation_tarifs",array("manifestationid" => $manifid, "tarifid" => intval($tarifid))) !== false;
			if ( $prix["value"] !== "" && $prix["value"] != $prix["default"] )
				$ok = $bd->addRecord("manifestation_tarifs",array("manifestationid" => $manifid, "tarifid" => intval($tarifid), "prix" => floatval($prix["value"]))) && $ok;
		}
		
		if($ok)	$user->addAlert("Tarifs mis à jour.");
		else	$user->addAlert("Echec de la mise à jour des tarifs");
	}
	
	if ( isset($_POST["submit"]) && isset($_POST["org"]) && isset($_POST["manif"]) && $mod )
	{
		// MAJ des co-organisateurs
		if ( $bd->delRecordsSimple("manif_organisation",array("manifid" => $manifid)) === false )
			$user->addAlert("Impossible de nettoyer la liste des co-organisateurs.");
		
		if ( is_array($_POST["org"]["org"]) )
		foreach ( $_POST["org"]["org"] as $coorga )
		if ( intval($coorga["value"]) > 0 )
			if ( !$bd->addRecord("manif_organisation",array("manifid" => $manifid, "orgid" => intval($coorga["value"]))) )
				$user->addAlert("Impossible d'ajouter un co-organisateur (id: ".intval($coorga["value"]).").");
		
		// MAJ de la TVA et autres
		$maj = array();
		if ( isset($_POST["manif"]["tva"]["value"]) || isset($_POST["manif"]["tva"]["default"]) )
			$maj["txtva"] = $_POST["manif"]["tva"]["value"] ? $_POST["manif"]["tva"]["value"] : $_POST["manif"]["tva"]["default"];
		if ( isset($_POST["manif"]["colorid"]) )
			$maj["colorid"] = $_POST["manif"]["colorid"] == 0 ? NULL : intval($_POST["manif"]["colorid"]);
		if ( isset($_POST["manif"]["plnum"]) )
			$maj["plnum"] = 't';
		else	$maj["plnum"] = 'false';
		if ( count($maj) > 0 ) if ( !$bd->updateRecordsSimple("manifestation",array("id" => $manifid),$maj) )
			$user->addAlert("Impossible de modifier le taux de TVA et/ou la couleur et/ou le placement à appliquer à cette manifestation");
	}
	
	// on affiche les détails si demandé :
	$more = isset($_GET["more"]);
?>
<script type="text/javascript">
  $(document).ready(function(){
    $('.bilan .toggle').click(function(){
      $(this).parent().parent().toggleClass('nodetails');
    });
  });
</script>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php
	$action = $actions["view"];
	require("actions.php");
	if ( $mod ) $action = $actions["edit"];
?>
<div class="body">
<form class="manif" name="formu" method="post" action="<?php echo htmlsecure($_SERVER["PHP_SELF"]).'?evtid='.$id.'&id='.$manifid; ?>">
	<h2>Fiche manifestation</h2>
	<?php
		// recup des données
		$query = " SELECT *
			   FROM info_resa
			   WHERE manifid = ".$manifid;
		$request = new bdRequest($bd,$query);
		$rec = $request->getRecord();
		$request->free();
	?>
	<div class="infos">
		<p class="titre">Informations générales</p>
		<div class="clip">
			<p class="global">
				<span></span><span><?php echo '<a href="evt/infos/fiche.php?id='.intval($rec["id"]).'&view">'.htmlsecure($rec["nom"]).'</a>' ?></span>
				<span class="manifid">(#<?php echo intval($rec["manifid"]) ?>)</span>
			</p>
			<p class="site"><span>Site:</span> <span><?php echo '<a href="evt/infos/salle.php?id='.intval($rec["siteid"]).'&view">'.htmlsecure($rec["sitenom"])."</a> (".htmlsecure($rec["ville"]).")" ?></span></p>
			<p class="date"><span>Date: </span> <span><?php echo $config["dates"]["dotw"][intval(date('w',strtotime($rec["date"])))].' '.date($config["format"]["date"]." à ".$config["format"]["ltltime"],strtotime($rec["date"])) ?></span></p>
			<p class="duree"></p>
			<p class="organisme">
				<span>Organisateurs:</span>
				<span><?php
					$onchange = "javascript: ttt_cloneOrg(this);";
					$orgid = 0;
					$categories = new bdRequest($bd," SELECT * FROM org_categorie ORDER BY libelle");
					$organismes = new bdRequest($bd," SELECT * FROM organisme_categorie ORDER BY nom, ville");
					
					if ( $mod )
					includePage("../../gen/linkorg");
				?></span>
				<?php
					$query = " SELECT org.id
						   FROM manif_organisation AS orga, organisme AS org
						   WHERE org.id = orga.orgid
						     AND orga.manifid = ".$manifid."
						   ORDER BY org.nom, org.ville";
					$request = new bdRequest($bd,$query);
					while ( ($orgid = intval($request->getRecordNext("id"))) > 0 )
					{
						echo '<span class="old">';
						includePage("../../gen/linkorg");
						echo '</span>';
					}
					$request->free();
					
					$tva = $rec["txtva"] ? floatval($rec["txtva"]) : floatval($rec["deftva"]);
				?>
			</p>
			<p class="jauge"><span><span>Jauge: </span><span class="jauge"><?php printJauge($rec["jauge"],$rec["preresas"],$rec["resas"],180,$rec["commandes"],220,$user); ?></span></span></p>
			<?php if ( $config["ticket"]["placement"] ) { ?>
			<p class="places">
				<span>Placement numéroté ?</span>
				<span><input type="checkbox" name="manif[plnum]" value="yes" <?php if ( $rec["plnum"] == "t" ) echo 'checked="checked"' ?> /></span>
				<span class="desc">À ne plus changer après l'édition du premier billet numéroté pour éviter les incohérences</span>
			</p>
			<?php } ?>
			<p class="tva"><span>TVA: </span><span><?php printField($name = "manif[tva]",$rec["txtva"],floatval($rec["deftva"]),8,4); ?>%</span><span class="desc">À ne plus changer après l'édition du premier billet au risque de fausser tous les prix</span></p>
			<p class="desc"><?php echo htmlsecure($rec["manifdesc"] ? $rec["manifdesc"] : "Pas de description") ?></p>
			<p class="color">
				<span>Couleur:</span>
				<?php
					$colors = new bdRequest($bd,"SELECT * FROM colors");
					while ( $color = $colors->getRecordNext() )
					{
				?>
				<span class="<?php echo $buf = htmlsecure($color["libelle"]) ?> color"><input
					<?php if ( !$mod ) echo 'disabled="disabled"'; ?>
					type="radio"
					name="manif[colorid]"
					value="<?php echo intval($color["id"]) ?>"
					<?php if ( $rec["colorname"] == $color["libelle"] ) echo 'checked="checked"'; ?>
				/><span class="hidden"><?php echo htmlsecure($color["libelle"]) ?></span></span>
				<?php
					}
					$colors->free();
				?>
			</p>
		</div>
	</div>
	<div class="tarifs">
		<p class="titre">Tarifs</p>
		<div class="clip"><ul><?php
			$bilan = false;
			$query = " SELECT *, (SELECT count(*) > 0 FROM reservation_pre WHERE manifid = tarif.manifid AND tarif.id = tarifid) AS stop
				   FROM tarif_manif AS tarif
				   WHERE manifid = ".$manifid."
				     AND NOT desact
				   ORDER BY prix, key";
			$def = new bdRequest($bd,$query);
			$prix = array();
			while ( $rec = $def->getRecordNext() )
			{
				if ( $rec["stop"] == 't' )
				{
					$action = $actions["view"];
					$bilan = true;
				}
				else	$action = $actions["edit"];
				echo '<li>';
				echo '<span>'.htmlsecure($rec["key"]).'</span><span>(';
				echo htmlsecure(strlen($rec["description"]) > 20 ? substr($rec["description"],0,20).".." : $rec["description"]);
				echo ')</span>';
				echo '<span>: ';
				if ( $action != $actions["view"] && $mod )
					printField($name = "prix[".$rec["id"]."]",!is_null($rec["prixspec"]) ? round($rec["prixspec"],2) : "", round(floatval($rec["prix"]),2),8,4);
				else	echo round($prix[$rec["key"]] = !is_null($rec["prixspec"]) ? floatval($rec["prixspec"]) : floatval($rec["prix"]),2);
				echo ' €</span>';
				echo '</li>';
			}
			$def->free();
		?></ul></div>
	</div>
	<p class="submit"><input type="submit" name="submit" value="Valider" /></p>
</form>
<?php if ( $bilan ) { ?>
<form action="evt/infos/group.hide.php" method="post">
	<div class="bilan">
		<input type="hidden" name="manifid" value="<?php echo $manifid ?>" />
		<p class="titre">Bilan</p>
		<div class="clip">
			<?php
				if ( !$more )
				{
					echo '<p class="more">';
					echo '<a href="'.$_SERVER["PHP_SELF"].'?evtid='.$id.'&id='.$manifid.'&more">Afficher les détails</a>';
					echo '</p>';
				}
				else echo '<p class="toggle">Cacher / Afficher</p>';
			?>
			<?php require("personnes.hide.php"); ?>
			<hr />
			<?php require("places.hide.php"); ?>
			<hr />
			<?php require("persplace.hide.php"); ?>
		</div>
	</div>
</form>
<?php } ?>
</div>
<?php
	includeLib("footer");
	$bd->free()
?>
