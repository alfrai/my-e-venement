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
*    Copyright (c) 2006-2009 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	require_once("../ann/conf.inc.php");
	includeClass("bdRequest");
	includeClass("navigation");
	
	$nav	= new navigation();
	$nav->mimeType("text/css","UTF-8");
	
	$bd	= new Bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
  $params = array();
  $query  = " SELECT * FROM options WHERE key LIKE 'labels.%'";
  $request = new bdRequest($bd,$query);
  while ( $rec = $request->getRecordNext() )
    $params[substr($rec['key'],7)] = $rec['value'];
  $request->free();
?>
* { padding: 0; margin: 0; }
body.labels .page .labels {
  margin-top: <?php echo $ptop = floatval($params['top-bottom'])-floatval($params['printer-y']) > 0 ? floatval($params['top-bottom'])-floatval($params['printer-y']) : 0 ?>mm;
}
body.labels .page {
  margin-left: <?php echo $pleft = floatval($params['left-right'])-floatval($params['printer-x']) > 0 ? floatval($params['left-right'])-floatval($params['printer-x']) : 0 ?>mm;
  height: <?php echo $height = floatval($params['height']) - floatval($params['printer-y'])*2 ?>mm;
  page-break-after: always;
  overflow: hidden;
}
body.labels .page.last-child { page-break-after: auto; height: <?php echo $height - 2 ?>mm }
body.labels {
  width: <?php echo $width = floatval($params['width']) - floatval($params['printer-x'])*2 - $pleft*2 ?>mm;
}
body.labels .labels { display: table; }
body.labels .labels > li { display: table-row; }
body.labels .labels > li > div { display: table-cell; }

body.labels .labels > li > div {
  width:  <?php echo round(( $width-$pleft*2-floatval($params['margin-x'])*(intval($params['nb-x'])-1) )/intval($params['nb-x'])) ?>mm;
  height: <?php echo $cellheight = round(( $height-$ptop*2-floatval($params['margin-y'])*(intval($params['nb-y'])-1) )/intval($params['nb-y'])) ?>mm;
  overflow: hidden;
  vertical-align: middle;
}
body.labels .labels > li > div.margin {
  width: <?php echo floatval($params['margin-x']) ?>mm;
  outline: 0;
  height: 0;
}
body.labels .labels > li > div div.content {
  height: <?php echo $cellheight - floatval($params['padding-y'])*2 ?>mm;
  padding: <?php echo floatval($params['padding-y']).'mm '.floatval($params['padding-x']).'mm' ?>;
  overflow: hidden;
}

/* compensating printer margins */
body.labels .labels > li > div:first-child div.content {
  padding-left: <?php echo $pleft+floatval($params['padding-x']) < 0 ? 0 : $pleft+floatval($params['padding-x']) ?>mm;
}
body.labels .labels > li > div:last-child div.content {
  padding-right: <?php echo $pleft+floatval($params['padding-x']) < 0 ? 0 : $pleft+floatval($params['padding-x']) ?>mm;
}

/* text style */
body.labels { font-size: 12px; }
body.labels .labels > li .content p { text-align: center; }
body.labels .labels > li .content .org { font-weight: bold; }
body.labels .labels > li .content .org { text-transform: uppercase; }
body.labels .labels > li .content .tels,
body.labels .labels > li .content .email,
body.labels .labels > li .content .pro { font-size: 9px; }

<?php $bd->free(); ?>
