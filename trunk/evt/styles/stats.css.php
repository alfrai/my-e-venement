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
?>

table.stats { background-color: white; margin-right: 10px; }
table.stats tbody td.gfx {
  vertical-align: bottom;
  width: 90px;
}
table.stats tbody td.gfx tr.gfx span { padding-left: 25px; }
table.stats tbody td.gfx td { text-align: center; font-size: 8px; }
table.stats tbody tr.gfx td.demanded span  { background-color: blue; }
table.stats tbody tr.gfx td.preselled span { background-color: orange; }
table.stats tbody tr.gfx td.printed span   { background-color: red; }
table.stats tfoot td { text-align: center; }

.stats form span.input { border: 1px solid black; padding: 0 0 2px 5px; }
.stats form span.input input { border: 0; width: 100px; }

<?php	$bd->free(); ?>

