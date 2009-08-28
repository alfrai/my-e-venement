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
	
	$args = split("/",$_SERVER["PATH_INFO"]);
	$id = intval($args[1]);
	$nav->mimeType("text/css","UTF-8");
	
	if ( !file_exists("salle-".$id.".png") )
		exit(0);
	$img = getimagesize("salle-".$id.".png");
	
	$bd->free();
?>
div.body {
	background: transparent url(../salle-<?php echo $id ?>.png) no-repeat scroll top center;
	width: <?php echo intval($img[0]) ?>px;
	height: <?php echo intval($img[1]) ?>px;
	border-top: 1px solid gray;
	overflow: auto;
	padding: 0;
}

/* non dynamique */
#plnumedit, #close {
	position: absolute;
	top: 10px;
	right: 20px;
}
#plnumedit { right: 45px; }
#close span { display: none; }
#close a {
	padding: 0 8px;
	background: transparent url(../../../img/close.png) no-repeat scroll bottom center;
}
div.plnum {
	position: absolute;
	top: 10px;
	left: 400px;
	border: 1px dashed black;
	width: 220px;
	padding: 0 10px;
	z-index: 10;
	background-color: silver;
}
div.body #num {
	position: absolute;
	top: 2px;
	right: 2px;
	border: 1px dashed gray;
	min-width: 36px;
	min-height: 18px;
	padding-left: 2px;
	z-index: 10;
}
div.body #area {
	display: none;
	width: 10px;
	height: 10px;
	border: 1px dotted black;
	position: absolute;
}
div.body #area.visible { display: block; }
#mapping #placesample { display: none; }
#mapping div.place span { display: none; }
body.edit #mapping div.place {
	background-color: yellow;
	border: 1px solid orange;
}
#mapping div.place {
	position: absolute;
	z-index: 20;
}
body.edit #mapping div.place:hover,
#mapping div.place:hover {
	background-color: transparent;
	cursor: pointer;
	border: 1px solid orange;
}
#mapping div.place.selected { border: 1px solid orange; }
#mapping div.place.reserved { background-color: red; border: 1px solid transparent; }
#mapping div.place.reserved:hover {
  background-color: transparent;
  border: 1px solid red;
  cursor: default;
}

#alert {
	display: none;
	border: 2px solid gray;
	position: absolute;
	z-index: 10;
	top: 80px;
	left: 350px;
	padding: 2px 4px;
	background-color: white;
	font-weight: bold;
}
#alert.done, #alert.err { display: block; }
#alert.done { border-color: green; }
#alert.err {
	border-color: red;
	text-decoration: blink;
}
