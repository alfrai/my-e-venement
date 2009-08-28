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
	$config["website"]["libs"] .= ":".$_SERVER["DOCUMENT_ROOT"].$config["website"]["root"]."sco/libs/:".$_SERVER["DOCUMENT_ROOT"].$config["website"]["root"]."evt/libs/";	// extend lib path to particular libs
	
	$config["sco"]["right"]["param"]	= 10;	// droit de paramétrer
	$config["sco"]["right"]["mod"]		= 5;	// droit de modifier (ajout, modifications, suppressions)
	$config["sco"]["right"]["view"]		= 3;	// droit de lire
	
	$config["sco"]["export"]["ack"]		= "(acceptés)";
	$config["sco"]["export"]["trans"]	= "(transposés)";
	$config["sco"]["export"]["nack"]	= "(non-acceptés)";
	
	ini_set("include_path",$config["website"]["libs"].":.");
?>
