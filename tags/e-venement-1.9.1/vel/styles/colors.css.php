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
	includeClass("navigation");
	
	$nav	= new navigation();
	$nav->mimeType("text/css","UTF-8");
	
	$bd	= new Bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	$bd->setPath("billeterie,public");
	
	$query = " SELECT * FROM color";
	$request = new bdRequest($bd,$query);
	
	while ( $rec = $request->getRecordNext() )
	{
?>
	option.<?php echo $rec["libelle"] ?> { background-color: #<?php echo $rec["color"] ?>; }
<?php
	}
?>

<?php	$bd->free(); ?>

