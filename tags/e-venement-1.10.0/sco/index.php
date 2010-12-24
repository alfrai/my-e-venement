<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    beta-libs is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with beta-libs; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	require("conf.inc.php");
	
	function export($query, $grpid, $grpname)
	{
		global $bd, $config, $nav, $user;
		$request = new bdRequest($bd,$query);
		
		$ok = false;
		
		while ( $rec = $request->getRecordNext() )
		if ( $rec["fctorgid"] )
			$ok = $bd->addRecord("groupe_fonctions",array("groupid" => $grpid, "fonctionid" => intval($rec["fctorgid"]), "included" => "t", "info" => $rec["nbtickets"]));
		else	$ok = $bd->addRecord("groupe_personnes",array("groupid" => $grpid, "personneid" => $rec["personneid"], "included" => "t", "info" => $rec["nbtickets"]));
		
		if ( !$ok ) $user->addAlert("Export échoué, sans doute parce qu'il n'y a rien à exporter.");
		else
		{
			$user->addAlert("Export réussi");
			$request->free();
			$bd->endTransaction();
			$nav->redirect($config["website"]["base"]."ann/new-search.php?grpid=".$grpid."&grpname=".urlencode($grpname),"Export réussi : redirection vers le groupe créé");
		}
		
		$request->free();
	}
	
	// l'export à proprement parlé
	if ( isset($_GET["export"]) && intval($_GET["evtid"]) > 0 )
	{
		$query = " SELECT * FROM evenement WHERE id = ".intval($_GET["evtid"]);
		$request = new bdRequest($bd,$query);
		$grpname = "[sco] ".$request->getRecord("nom")." ".$config["sco"]["export"][$_GET["export"]];
		$request->free();
		
		$bd->beginTransaction();
		$bd->delRecordsSimple("groupe",array("nom" => $grpname, "createur" => $user->getId()));
		if ( $bd->addRecord("groupe",array("nom" => $grpname, "createur" => $user->getId(), "description" => 'Groupe créé à partir du module "sco"')) )
		{
			$grpid = $bd->getLastSerial("groupe","id");
			
			switch ( $_GET["export"] ) {
			case "ack":
				$query	= " SELECT personneid, fctorgid, sum(ticket.nb) AS nbtickets
					    FROM evenement AS evt, manifestation AS manif, tableau_manif AS tabmanif, entry, tableau_personne AS tabpers, ticket
					    WHERE evt.id = ".intval($_GET["evtid"])."
					      AND evt.id = manif.evtid
					      AND tabmanif.manifid = manif.id
					      AND entry.tabmanifid = tabmanif.id
					      AND entry.tabpersid = tabpers.id
					      AND ticket.entryid = entry.id
					      AND valid
					   GROUP BY personneid, fctorgid
					   ORDER BY personneid, fctorgid, nbtickets";
				export($query,$grpid,$grpname);
				break;
			case "trans":
				$query	= " SELECT personneid, fctorgid, sum(ticket.nb) AS nbtickets
					    FROM evenement AS evt, manifestation AS manif, tableau_manif AS tabmanif, entry, tableau_personne AS tabpers, ticket
					    WHERE evt.id = ".intval($_GET["evtid"])."
					      AND evt.id = manif.evtid
					      AND tabmanif.manifid = manif.id
					      AND entry.tabmanifid = tabmanif.id
					      AND entry.tabpersid = tabpers.id
					      AND ticket.entryid = entry.id
					      AND valid
					      AND tabpers.transposed IS NOT NULL
					    GROUP BY personneid, fctorgid
					    ORDER BY personneid, fctorgid, nbtickets";
				export($query,$grpid,$grpname);
				break;
			case "nconf":
				$query	= " SELECT personneid, fctorgid, sum(ticket.nb) AS nbtickets
					    FROM evenement AS evt, manifestation AS manif, tableau_manif AS tabmanif, entry, tableau_personne AS tabpers, ticket
					    WHERE evt.id = ".intval($_GET["evtid"])."
					      AND evt.id = manif.evtid
					      AND tabmanif.manifid = manif.id
					      AND entry.tabmanifid = tabmanif.id
					      AND entry.tabpersid = tabpers.id
					      AND ticket.entryid = entry.id
					      AND valid
					      AND tabpers.transposed IS NOT NULL
					      AND NOT tabpers.confirmed
					    GROUP BY personneid, fctorgid
					    ORDER BY personneid, fctorgid, nbtickets";
				export($query,$grpid,$grpname);
				break;
			case "nack":
				$query	= " SELECT personneid, fctorgid, sum(ticket.nb) AS nbtickets
					    FROM evenement AS evt, manifestation AS manif, tableau_manif AS tabmanif, entry, tableau_personne AS tabpers, ticket
					    WHERE evt.id = ".intval($_GET["evtid"])."
					      AND evt.id = manif.evtid
					      AND tabmanif.manifid = manif.id
					      AND entry.tabmanifid = tabmanif.id
					      AND entry.tabpersid = tabpers.id
					      AND ticket.entryid = entry.id
					      AND NOT valid
					    GROUP BY personneid, fctorgid
					    ORDER BY personneid, fctorgid, nbtickets";
				export($query,$grpid,$grpname);
				break;
			}
		}
		else	$user->addAlert("Impossible de créer le groupe désiré, export interrompu.");
		$bd->endTransaction();
	}
	
	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php includePage("actions"); ?>
<div class="body">
<h2>Scolaires & Groupes</h2>
<?php @include("desc.txt"); ?>
<h3>Récapitulatif des entrées existantes</h3>
<div class="entries">
<?php
	$query	= " SELECT DISTINCT account.name, tableau.*, evt.nom AS evtnom, evt.id AS evtid
		    FROM tableau, account, tableau_manif, manifestation AS manif, evenement AS evt
		    WHERE account.id = tableau.accountid
		      AND tableau_manif.tableauid = tableau.id
		      AND tableau_manif.manifid = manif.id
		      AND manif.evtid = evt.id
		    ORDER BY creation, modification";
	$request = new bdRequest($bd,$query);
	for ( $last = 0 ; $rec = $request->getRecordNext() ; )
	{
		if ( $last != intval($rec["id"]) )
		{
			if ( $last != 0 ) echo '</p>';
			$last = intval($rec["id"]);
			
?>
<p class="entry">
	<a class="del" href="sco/fiche.php?id=<?php echo intval($rec["id"]) ?>&del"><span>Supprimer</span></a>
	<a class="next" href="sco/fiche.php?id=<?php echo intval($rec["id"]) ?>"><span>Consulter</span>#<?php echo intval($rec["id"]) ?></a>
	<span class="crea"><?php echo date($config["format"]["date"],strtotime($rec["creation"])) ?></span>
	<span class="mod"><?php echo date($config["format"]["date"],strtotime($rec["modification"])) ?></span>
	<span class="user"><?php echo htmlsecure($rec["name"]) ?></span>
	<span class="evt"><a href="evt/infos/fiche.php?id=<?php echo intval($rec["evtid"]) ?>"><?php echo htmlsecure($rec["evtnom"]) ?></a>
<?php
		}
		else	echo '<a class="evt" href="evt/infos/fiche.php?id='.intval($rec["evtid"]).'">'.htmlsecure($rec["evtnom"]).'</a> ';
		
	}
	if ( $last != 0 ) echo '</span></p>';
	$request->free();
?>
</div>
<h3>Récapitulatifs par personne</h3>
<form class="personnes" action="<?php echo htmlsecure($_SERVER["PHP_SELF"]); ?>" method="post">
	<?php $pers = $_GET["pers"] ? $_GET["pers"] : $_POST["pers"]; ?>
	<p>
		<span>Entrer le nom de la personne recherchée&nbsp;:</span>
		<span><input type="text" name="pers" value="<?php echo htmlsecure($pers) ?>" /></span><span class="desc">et valider...</span>
		<span><input type="submit" name="submit" value="ok" /></span>
	</p>
	<?php
		if ( $pers )
		{
			$query	= " SELECT DISTINCT personne.*
				    FROM personne_properso AS personne, tableau_personne AS tabpers
				    WHERE tabpers.personneid = personne.id
				      AND (tabpers.fctorgid = personne.fctorgid OR tabpers.fctorgid IS NULL AND personne.fctorgid IS NULL)
				      AND personne.nom ILIKE '".pg_escape_string($pers)."%'";
			$request = new bdRequest($bd,$query);
			
			echo '<ul id="personnes">';
			while ( $rec = $request->getRecordNext() )
			{
				$fct = $rec["fctdesc"] ? $rec["fctdesc"] : $rec["fcttype"];
				echo '<li>';
				echo '<a class="next" href="sco/personne.php?persid='.intval($rec["id"]).'&fctorgid='.intval($rec["fctorgid"]).'"><span>Consulter</span></a>';
				echo '<a href="ann/fiche.php?id='.intval($rec["id"]).'">'.htmlsecure($rec["nom"].' '.$rec["prenom"]).'</a> ';
				if ( intval($rec["orgid"]) > 0 )
				echo '(<a href="org/fiche.php?id='.intval($rec["orgid"]).'">'.htmlsecure($rec["orgnom"]).'</a>'.($fct ? htmlsecure(' - '.$fct) : '').')';
				echo '</li>';
			}
			echo '</ul>';
			
			$request->free();
		}
	?>
</form>
<h3>Export des données du module</h3>
<ul class="evts">
	<?php
		$query	= " SELECT DISTINCT evt.*
			    FROM tableau_manif AS tabmanif, manifestation AS manif, evenement AS evt
			    WHERE manifid = manif.id
			      AND manif.evtid = evt.id
			    ORDER BY evt.nom";
		$request = new bdRequest($bd,$query);
		
		while ( $rec = $request->getRecordNext() )
		{
			echo '<li>';
			echo '<a href="evt/infos/fiche.php?id='.intval($rec["id"]).'">'.htmlsecure($rec["nom"]).'</a>: ';
			
			// acceptés
			echo '<a href="'.htmlsecure($_SERVER["PHP_SELF"]).'?evtid='.intval($rec["id"]).'&export=ack" class="accepted">acceptés</a>';
			
			// transposés
			echo ' (';
			echo '<a href="'.htmlsecure($_SERVER["PHP_SELF"]).'?evtid='.intval($rec["id"]).'&export=trans" class="transposed">transposés</a>';
			echo ', <a href="'.htmlsecure($_SERVER["PHP_SELF"]).'?evtid='.intval($rec["id"]).'&export=nconf" class="nconfirmed">non confirmés</a>';
			echo ')';
			
			// transition
			echo ' - ';
			
			// refusés
			echo '<a href="'.htmlsecure($_SERVER["PHP_SELF"]).'?evtid='.intval($rec["id"]).'&export=nack" class="nonaccepted">non-acceptés</a>';
			
			echo '</li>';
		}
		
		$request->free();
	?>
</ul>
</div>
<?php
	includeLib("footer");
?>
