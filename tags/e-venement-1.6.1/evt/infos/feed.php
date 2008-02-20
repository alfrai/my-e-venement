<?php
/**********************************************************************************
*
*	    This file is part of desoles.org.
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
	define('NBMANIFS',12);
	define('ALLOPEN',true);
	
	require("conf.inc.php");
	
	if ( !$config["evt"]["syndication"] )
		exit(1);
	
	includeClass("bd");
	includeClass("bdRequest");
	
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
			   site.nom AS sitenom, site.ville
		    FROM manifestation AS manif, evenement_categorie AS evt, site
		    WHERE evtid = evt.id
		      AND manif.siteid = site.id ";
	$query	= " SELECT * INTO TEMP feed
		    FROM (
		    (".$subq."
		      AND date <= NOW()
		    ORDER BY date DESC
		    LIMIT ".NBMANIFS.")
		   UNION
		    (".$subq."
		      AND date >= NOW()
		    ORDER BY date ASC
		    LIMIT ".(NBMANIFS/2).") ) AS tmp";
	$request = new bdRequest($bd,$query);
	$request->free();
	
	$query	= " SELECT * FROM feed";
	$request = new bdRequest($bd,$query);
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
			echo '<id>'.htmlsecure($config["website"]["base"].'evt/infos/manif.php?id='.intval($rec['manifid']).'&evtid='.intval($rec["id"])).'</id>';
			echo '<title>'.htmlsecure(date($config["format"]["date"].' '.$config["format"]["maniftime"],strtotime($rec["date"])).': '.$rec["nom"]).'</title>';
			echo '<link rel="alternate" type="text/html" href="'.htmlsecure($config["website"]["base"].'evt/infos/manif.php?id='.intval($rec['manifid']).'&evtid='.intval($rec["id"])).'"/>';
			echo '<updated>'.htmlsecure(date($config["format"]["atomdate"],strtotime($rec["updated"]))).'</updated>';
			echo '<summary type="xhtml">'.htmlsecure('Le '.date($config["format"]["date"].' '.$config["format"]["maniftime"],strtotime($rec["date"])).' à '.$rec["sitenom"].' - '.$rec["ville"]).'</summary>';
			echo '<content type="xhtml">'.htmlsecure($rec["description"]).'</content>';
			echo '<author><name>'.htmlsecure($config["divers"]["appli-name"]).'</name></author>';
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
