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
*    Copyright (c) 2007 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	define('NBMANIFS',intval($_GET["nb"]));
	define('ALLOPEN',true);
	
	require("conf.inc.php");
	
	if ( !$config["evt"]["syndication"] )
		exit(1);
	
	includeClass("bd");
	includeClass("bdRequest/array");
	
	header("Content-Type: application/atom+xml");
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
?>
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="fr">
	<generator>e-venement - by libre-informatique.fr</generator>
	<link rel="self" type="application/atom+xml" href="<?php echo $uri = htmlsecure($config["website"]["base"]).'evt/infos/feed.php?'.htmlsecure($_SERVER["QUERY_STRING"]) ?>"/>
<?php
	$subq	= " SELECT evt.*, manif.id AS manifid, manif.date, manif.updated, manif.created,
			   site.nom AS sitenom, site.ville,
			   (SELECT nom FROM organisme WHERE organisme1 = id) AS orgnom1,
			   (SELECT nom FROM organisme WHERE organisme2 = id) AS orgnom2,
			   (SELECT nom FROM organisme WHERE organisme3 = id) AS orgnom3
		    FROM manifestation AS manif, evenement_categorie AS evt, site
		    WHERE evtid = evt.id
		      AND manif.siteid = site.id ";
	$query	= " SELECT * INTO TEMP feed
		    FROM (
		    (".$subq."
		      AND date <= NOW()
		    ORDER BY date DESC
		    ".(NBMANIFS > 0 ? " LIMIT ".NBMANIFS : "")." )
		   UNION
		    (".$subq."
		      AND date >= NOW()
		    ORDER BY date ASC
		    ".(NBMANIFS > 0 ? " LIMIT ".NBMANIFS : "")." )) AS tmp";
	$request = new bdRequest($bd,$query);
	$request->free();
	
	if ( isset($_GET["evt"]) )
	{
		$query	= " SELECT id, nom, description, textede, textede_lbl, ages, typedesc, duree,
			           tarifweb, tarifwebgroup, extradesc, extraspec, imageurl, orgnom1, orgnom2, orgnom3,
			           min(date) AS date, min(updated) AS update
			    FROM feed";
		if ( $_GET["cat"] || $_GET["metaevt"] ) $query .= " WHERE ";
		if ( $_GET["cat"] )
		$query .= " categorie IN (SELECT id FROM evt_categorie WHERE libelle = '".pg_escape_string($_GET["cat"])."')";
		if ( $_GET["cat"] || $_GET["metaevt"] ) $query .= " AND ";
		if ( $_GET["metaevt"] )
		$query .= " metaevt ILIKE '".pg_escape_string($_GET["metaevt"])."%'";
		$query .= " GROUP BY id, nom, description, textede, textede_lbl, ages, typedesc, duree, tarifweb, tarifwebgroup, extradesc, extraspec, imageurl, orgnom1, orgnom2, orgnom3
			    ORDER BY date ASC, nom";
	}
	else
	{
		$query	= " SELECT *
			    FROM feed";
		if ( $_GET["cat"] || $_GET["metaevt"] )
		$query .= " WHERE ";
		if ( $_GET["cat"] )
		$query .= " categorie IN (SELECT id FROM evt_categorie WHERE libelle = '".pg_escape_string($_GET["cat"])."')";
		if ( $_GET["metaevt"] )
		$query .= " metaevt ILIKE '".pg_escape_string($_GET["metaevt"])."%'";
		$query .= " ORDER BY nom, date ASC";
	}
	$request = new arrayBdRequest($bd,$query);
?>
	<id><?php echo $uri ?></id>
	<title>e-venement - Derniers spectacles</title>
	<link rel="alternate" type="text/html" href="<?php echo htmlsecure($config["website"]["base"]) ?>evt/infos/agenda.php"/>
<?php
	if ( $request->countRecords() > 0 )
	{
		echo '<updated>'.htmlsecure(date($config["format"]["atomdate"]),strtotime("now"/*$request->getRecord("lastmod")*/)).'</updated>'."\n";
		while ( $rec = $request->getRecordNext() )
		{
			echo '<entry>';
			echo '<id>'.htmlsecure($config["website"]["base"].'evt/infos/'.(isset($_GET["evt"]) ? 'fiche.php?id='.intval($rec["id"]) : 'manif.php?id='.intval($rec['manifid']).'&evtid='.intval($rec["id"]))).'</id>';
			echo '<title>'.htmlsecure($rec["nom"]).'</title>';
			echo '<link rel="alternate" type="text/html" href="'.htmlsecure($config["website"]["base"].'evt/infos/'.(isset($_GET["evt"]) ? 'fiche.php?id='.intval($rec["id"]) : 'manif.php?id='.intval($rec['manifid']).'&evtid='.intval($rec["id"]))).'"/>';
			echo '<updated>'.htmlsecure(date($config["format"]["atomdate"],strtotime($rec["updated"]))).'</updated>';
			echo '<summary type="xhtml">'.htmlsecure('Le '.date($config["format"]["date"].' '.$config["format"]["maniftime"],strtotime($rec["date"])).' à '.$rec["sitenom"].' - '.$rec["ville"]).'</summary>';
			echo '<content type="xhtml">'.htmlsecure($rec["description"]).'</content>';
			echo '<author><name>'.htmlsecure($config["divers"]["appli-name"]).'</name></author>';
			echo '<eventdate>'.htmlsecure(date('Y-m-d H:i',strtotime($rec["date"]))).'</eventdate>';
			echo '<eventid>'.intval($rec["id"]).'</eventid>';
			
			if ( isset($_GET["ext"]) )
			{
				// de base
				echo '	<ext-textede>'.htmlsecure($rec["textede_lbl"].' '.$rec["textede"]).'</ext-textede>
					<ext-typedesc>'.htmlsecure($rec["typedesc"]).'</ext-typedesc>
					<ext-tarif>'.htmlsecure($rec["tarifweb"]).'</ext-tarif>
					<ext-tarifgrp>'.htmlsecure($rec["tarifwebgroup"]).'</ext-tarifgrp>
					<ext-extradesc>'.htmlsecure($rec["extradesc"]).'</ext-extradesc>
					<ext-extraspec>'.htmlsecure($rec["extraspec"]).'</ext-extraspec>
					<ext-duree>'.htmlsecure($rec["duree"]).'</ext-duree>
					<ext-imageurl>'.htmlsecure($rec["imageurl"]).'</ext-imageurl>';
				
				// les ages
				if ( $rec["ages"][0] > 0 )
				echo '<ext-agemin>'.htmlsecure($rec["ages"][0] >= 2 ? intval($rec["ages"][0])." ans" : (round($rec["ages"][0]*12))." mois").'</ext-agemin>';
				if ( $rec["ages"][1] )
				echo '<ext-agemax>'.htmlsecure($rec["ages"][1] >= 2 ? intval($rec["ages"][1])." ans" : (round($rec["ages"][1]*12))." mois").'</ext-agemax>';
				
				// les seances les lieux
				$query	= " SELECT DISTINCT colorid, date, site.nom, site.adresse, site.cp, site.ville, site.pays
					    FROM manifestation, site
					    WHERE siteid = site.id AND evtid = ".intval($rec["id"]);
				$manifs = new bdRequest($bd,$query);
				
				$salles = array();
				while ( $manif = $manifs->getRecordNext() )
				{
					if ( !in_array($manif["nom"],$salles) )
					{
						$salles[] = $manif["nom"];
						echo '<ext-salle>'.htmlsecure('<a href="http://maps.google.fr/maps?f=q&hl=fr&q='.urlencode($manif["adresse"].", ".$manif["cp"]." ".$manif["ville"].", ".$manif["pays"]).'">'.$manif["nom"].'</a>').'</ext-salle>';
					}
					echo '<ext-seance-'.intval($manif["colorid"]).'>'.htmlsecure(date('Y-m-d H:i',strtotime($manif["date"]))).'</ext-seance-'.intval($manif["colorid"]).'>';
				}
				
				$manifs->free();
				
				// les organismes / compagnies
				$orgs = array("orgnom1","orgnom2","orgnom3");
				foreach ( $orgs AS $orgnom )
					echo $rec[$orgnom] ? '<ext-org>'.htmlsecure($rec[$orgnom]).'</ext-org>' : '';
			}
			echo '</entry>';
			echo "\n";
		}
	}
?>
</feed>
<?php
	$request->free();
	$bd->free();
?>
