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
	
	if ( !$user->hasRight($config["right"]["group"]) )
	{
		if ( !isset($_GET["ajax"]) )
		{
			$user->addAlert("Impossible de créer votre groupe : vous n'en avez pas le droit");
			if ( $_SERVER["REFERER"] != $_SERVER["PHP_SELF"] )
				$nav->redirect($_SERVER["REFERER"]);
			else	$nav->redirect("ann/group.php");
		}
		else echo "false";
		exit(1);
	}
	
	$manifid = intval($_GET["manifid"]);
	
	$query	= " SELECT manif.id, evt.nom, manif.date
		    FROM evenement AS evt, manifestation AS manif
		    WHERE manif.evtid = evt.id
		      AND manif.id = ".$manifid;
	$request = new bdRequest($bd,$query);
	$manif["id"] = intval($request->getRecord("id"));
	$manif["nom"] = $request->getRecord("nom");
	$manif["time"] = strtotime($request->getRecord("date"));
	$request->free();
	
	$grpname = "manif #".$manifid." (".$manif["nom"].")";
	
	// suppression du groupe précédent
	if ( $bd->delRecordsSimple("groupe",array("nom" => $grpname, "createur" => $user->getId())) === false )
		$user->addAlert("Impossible de supprimer le groupe précédent");
	
	// création du groupe à proprement parlé
	$arr = array();
	$arr["createur"]	= $user->getId();
	$arr["nom"]		= $grpname;
	$arr["description"]	= $manif["nom"]." du ".date($config["format"]["date"]." à ".$config["format"]["maniftime"],$manif["time"]);
	$arr["description"]	.= " enregistré le ".date($config["format"]["date"]." à ".$config["format"]["ltltime"])." (généré depuis le module billetterie).";
	$bd->addRecord("groupe",$arr);
	$groupid = $bd->getLastSerial("groupe","id");
	
	$org = array();
	$pers = array();

	$query = " CREATE TEMP TABLE tickets AS
		    SELECT *
		    FROM tickets2print_bymanif(".$manifid.");
		   CREATE TEMP TABLE tmptab AS
		    SELECT sum(nb) AS nb, transac.id IN (SELECT transaction FROM contingeant) AS contingeant,
		           transac.id IN (SELECT transaction FROM masstickets) AS depot,
		           printed AND NOT canceled AS resa,
		           transac.id IN (SELECT transaction FROM preselled) AS preresa,
		           transac.id AS transaction, personne.id, personne.nom, personne.prenom, personne.orgid, personne.fctorgid, personne.orgnom, personne.fcttype, personne.fctdesc
		    FROM tickets AS resa, personne_properso AS personne, transaction AS transac
		    WHERE ( transac.personneid = personne.id OR personne.id IS NULL AND transac.personneid IS NULL)
		      AND transac.id = resa.transaction
		      AND ( personne.fctorgid = transac.fctorgid OR personne.fctorgid IS NULL AND transac.fctorgid IS NULL)
		    GROUP BY personne.id, personne.nom, personne.prenom, personne.orgid, personne.orgnom, contingeant, resa, preresa, transac.id, fcttype, fctdesc, personne.fctorgid
		    ORDER BY nom, prenom";
	$request = new bdRequest($bd,$query);
	$request->free();
	
	$query = " SELECT *
		   FROM tmptab
		   WHERE (preresa OR resa) AND NOT contingeant AND NOT depot";
	$request = new bdRequest($bd,$query);
	while ( $rec = $request->getRecordNext() )
	if ( intval($rec["fctorgid"]) > 0 )
		$org[] = intval($rec["fctorgid"]);
	else	$pers[] = intval($rec["id"]);
	$request->free();
	
	// ajout du contenu du groupe
	$nb_pers = 0;
	foreach ( $pers AS $persid )
	{
		$arr = array();
		$arr["groupid"] = $groupid;
		$arr["personneid"] = $persid;
		$arr["included"] = 't';
		if ( @$bd->addRecord("groupe_personnes",$arr) )
			$nb_pers++;
		
	}
	
	$nb_org = 0;
	foreach ( $org AS $fctorgid )
	{
		$arr = array();
		$arr["groupid"] = $groupid;
		$arr["fonctionid"] = $fctorgid;
		$arr["included"] = 't';
		if ( @$bd->addRecord("groupe_fonctions",$arr) )
			$nb_org++;
	}
	
	$bd->free();

	if ( !isset($_GET["ajax"]) )
		$nav->redirect($config["website"]["root"]."ann/search.php?grpid=".$groupid."&grpname=".urlencode($grpname));
	else	echo "true";
?>
