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
	$config["website"]["libs"] .= ":".$_SERVER["DOCUMENT_ROOT"].$config["website"]["root"]."/evt/libs/";	// extend lib path to particular libs
	$config["website"]["activecal"] = "/usr/share/www/activecalendar/source";	// path to activecalendar sources
	$config["website"]["artichow"] = "/usr/share/www/artichow/";				// path to artichow libs
	
	$config["ticket"]["width"]		= "500px";
	$config["ticket"]["height"]		= "140px";
	$config["ticket"]["titlemaxchars"]	= "26";
	$config['ticket']['more-mentions']      = 'licences: 350970 / 350971 / 350972';
	
	$config["ticket"]["placement"]	= false;	// fonctionnalité de placement numéroté
	$config["compta"]["defaulttva"]	= "2.10";
	$config["regional"]["decimaldelimiter"] = ",";
	
        $config["evt"]["syndication"]           = true;
        $config["evt"]["ext"]["web"]		= false;
        
        /**
	  * Possibilité ou non de sortir des billets groupés
	  * Afin de rester en phase avec le code général des impots
	  * (date: 26 juin 2007), il est impératif de laisser cette
	  * option à false... au risque d'être hors la loi. Bien que
	  * la licence GNU GPL spécifie bien "without any warranty",
	  * Libre Informatique ne pourrait en aucun cas être tenu
	  * responsable de l'utilisation de cette fonctionnalité qui
	  * n'engage que ses utilisateurs finaux !!
	  **/
	$config["ticket"]["enable_group"]	= false;
	$config['ticket']['enable_vertical'] = false;
	
	/**
	  * compte tenu de l'article 290 quater du CGI mis à jour le 31/12/2006
	  * il est dorénavant possible d'avoir une billetterie complètement dématérialisée
	  * autrement dit sans billet "officiel". Cette fonctionnalité permet d'activer
	  * cet aspect dans e-venement.
	  *
	  **/
	$config["ticket"]["dematerialized"]	= false;
	
	// les droits sur le module
	$config['evt']['right']['param']	= 10;		// droit de paramétrer le module
	$config['evt']['right']['unblock']= 8;		// droit de débloquer des opérations
	$config['evt']['right']['mod']		= 5;		// droit de modifier des données (suppr., ajout, modification)
	$config['evt']['right']['simple'] = 4;    // opérateur basique, vue simplifiée
	$config['evt']['right']['view']		= 3;		// droit de consulter

	@include(dirname(__FILE__).'/config.php');
	ini_set("include_path",$config["website"]["libs"].":.:".$config["website"]["activecal"].":".$config["website"]["artichow"]);
	
	if ( ALLOPEN !== true )
	require(dirname(__FILE__).'/secu.php');
?>
