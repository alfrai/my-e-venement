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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	// années passées
	$config["website"]["dirtopast"] = "past";
	if ( !isset($config['mail']['max_recipient']) )
	$config['mail']['max_recipient'] = 200;
	
	$config["divers"]["appli-name"]		= !isset($config["divers"]["appli-name"]) ? '<a href="http://www.libre-informatique.fr/sw/01-Billetterie/e-venement">e-venement</a>' : $config["divers"]["appli-name"];
  $config["divers"]["author-name"]  = '<a href="http://www.libre-informatique.fr/">Libre Informatique</a> - Baptiste SIMON';
  $config["divers"]["author-mail"]  = "bs-public AT e-glop.net";
  $config['divers']['docs']         = 'http://www.libre-informatique.fr/sw/01-Billetterie/e-venement/Manuels';
	
	// géo-localisation (google-map)
	$config["gmap"]["enable"]	= false;
	$config["gmap"]["key"]		= '';
	$config["gmap"]["zoomout"]	= 6;
	$config["gmap"]["zoom"]		= 13;
	$config["gmap"]["markers"]	= 5;
	$config["gmap"]["defcenter"][0]	= '47.998118';
	$config["gmap"]["defcenter"][1]	= '-4.097701';
	$config["gmap"]["perso_url"]	= 'http://maps.google.com/maps/ms?ie=UTF8&hl=fr&mid=1203000400&msa=0&msid=109071131383604672741.0004461f885edf8b54a7d';
	$config["gmap"]["perso_kml"]	= $config["gmap"]["perso_url"]."&output=nl";
	
	$config["format"]["sysdate"]		= "Y-m-d";
	$config["format"]["date"]		= "d/m/Y";
	$config["format"]["time"]		= "H:i:s";
	$config["format"]["ltltime"]		= "H:i";
	$config["format"]["maniftime"]		= "H:i";
	$config["format"]["atomdate"]           = "Y-m-d\TH:i:s+02:00";
	
	$config["dates"]["dotw"] = array("Dim","Lun","Mar","Mer","Jeu","Ven","Sam");
	$config["dates"]["DOTW"] = array("Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi");
	$config["dates"]["moty"] = array("Jan","Fév","Mar",
					 "Avr","Mai","Jun",
					 "Jul","Aoû","Sep",
					 "Oct","Nov","Déc");
	$config["dates"]["MOTY"] = array("Janvier","Février","Mars",
					 "Avril","Mai","Juin",
					 "Juillet","Août","Septembre",
					 "Octobre","Novembre","Décembre");
	
	$config["style"]["timer"]               = "styles/timer-sql.css";
	
	$default = array();
	$default["description"]	= "-petite description facultative-";
	$default["typedesc"]	= "-genre de spectacle-";
	$default["duree"]	= "-HH:MM:SS-";
	$default["date"]	= "-AAAA-MM-JJ HH:MM:SS-";
	$default["age_min"]	= "-min-";
	$default["age_max"]	= "-max-";
	$default["code"]	= "-F3-";
	$default["jauge"]	= "-jauge-";
	$default["textede_lbl"]	= '-Label ~Texte de~-';
	$default["textede"]	= "-Jean Martin-";
	$default["mscene_lbl"]	= '-Label ~Mise en scène~-';
	$default["mscene"]	= "-Florence Thomas-";
	$default["expire"]	= "-AAAA/MM/JJ-";
	$default["name"]	= "-Jade-";
	$default["login"]	= "-jade-";
	$default["password"]	= "-xxxxxx-";
	$default["cp"]		= "-29920-";
	$default["ville"]	= "-Bolazec-";
	$default["infcreation"] = "créa.";
	$default["infmodification"] = "mod.";
	$default["groupname"]	= "-nom du groupe-";
	$default["nom"]		= "-DUPORT-";
	$default["adresse"]	= "-3, rue du Stang-";
	$default["pays"]	= "-France-";
	$default["opennewpage"]	= "Ce lien est fait pour être ouvert dans un nouvel onglet... (ctrl+clic)";
	$default["commongrp"]	= "Les groupes communs";
	
	// les droits pour les différentes parties de l'application
	$config["right"]["devel"]	= 20;	// accéder au paramétrage de l'appli
	$config["right"]["param"]	= 10;	// accéder au paramétrage de l'appli
	$config["right"]["archives"]	= 8;	// droit d'accès aux archives
	$config["right"]["commongrp"]	= 7;	// droit de supprimer des groupes communs
	$config["right"]["group"]	= 3;	// droit de créer/modifier/supprimer des groupes
	$config["right"]["edit"]	= 5;	// droit de modifier les données
	$config["right"]["add"]		= 5;	// droit de modifier les données
	$config["right"]["del"]		= 5;	// droit de supprimer des données
	$config["right"]["view"]	= 0;	// droit de consulter les données
	
	$config['indispo'] = false;
	
	/**
	  * Configuration automatique
	  * 
	  **/
        ini_set("default_charset","UTF-8");
        
        // options de l'archivage
        @include("config.archive.php");
?>
