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
	$bd->setPath("sco,public");
?>

/* default */
form.entry p > span.pair { background-color: #e5e6ce; }
form.entry p.pair > span { background-color: #e5e6ce; }
form.entry p.pair > span.pair { background-color: #d9dbb5; }
form.entry p.pair > span.new { background-color: transparent; }

/* params */
<?php	
	$query = " SELECT name,value FROM params WHERE name LIKE 'color%'";
	$request = new bdRequest($bd,$query);
	while ( $rec = $request->getRecordNext() )
	{
		switch ( $rec["name"] ) {
		case 'colorcols':
			echo "form.entry p > span.pair { background-color: ".$rec["value"]."; }\n";
			break;
		case 'colorlines':
			echo "form.entry p.pair > span { background-color: ".$rec["value"]."; }\n";
			break;
		case 'colorinter':
			echo "form.entry p.pair > span.pair { background-color: ".$rec["value"]."; }\n";
			break;
		}
	}
	$bd->free();
?>

