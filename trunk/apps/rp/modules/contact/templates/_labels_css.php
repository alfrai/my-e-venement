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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
      <?php // those lines above come directly from e-venement v1.10 with only few modifications ?>
/* dynamic */

html body.labels {
  <?php if ( $opt = $params['font-family'] ): ?>
  font-family: <?php echo $opt ?>;
  <?php endif; ?>
  <?php if ( $opt = $params['font-size'] ): ?>
  font-size: <?php echo $opt ?>px;
  <?php endif; ?>
}

* { padding: 0; margin: 0; }
body.labels .page .labels {
  margin-top: <?php echo $ptop = floatval($params['top-bottom']) ?>mm;
}
body.labels .page {
  margin-left: <?php echo $pleft = floatval($params['left-right']) ?>mm;
  page-break-after: always;
  overflow: hidden;
}
body.labels .page.last-child { page-break-after: auto; }
body.labels .labels { display: block; }
body.labels .labels > div { display: block; }
body.labels .labels > div > div { display: inline-block; }

body.labels .labels > div > div {
  width:  <?php echo ( floatval($params['width'])-$pleft*2-floatval($params['margin-x'])*(intval($params['nb-x'])-1) )/intval($params['nb-x']) ?>mm;
  height: <?php echo $cellheight = ( floatval($params['height'])-$ptop*2-floatval($params['margin-y'])*(intval($params['nb-y'])-1) )/intval($params['nb-y']) ?>mm;
  overflow: hidden;
}
body.labels .labels > div > div > * {
  vertical-align: middle;
}
body.labels .labels > div > div.margin {
  width: <?php echo floatval($params['margin-x']) ?>mm;
  outline: 0;
  height: 0;
}
body.labels .labels > div > div div.content {
  padding: <?php echo floatval($params['padding-y']).'mm' ?> 0; /*.floatval($params['padding-x']).'mm' ?>; */
  overflow: hidden;
}

/* compensating printer margins
body.labels .labels > div > div:first-child div.content {
  padding-left: <?php echo $pleft+floatval($params['padding-x']) < 0 ? 0 : $pleft+floatval($params['padding-x']) ?>mm;
}
body.labels .labels > div > div:last-child div.content {
  padding-right: <?php echo $pleft+floatval($params['padding-x']) < 0 ? 0 : $pleft+floatval($params['padding-x']) ?>mm;
}
*/

/* text style */
body.labels { font-size: 12px; }
body.labels .labels > div .content p { text-align: center; }
body.labels .labels > div .content .org { font-weight: bold; }
body.labels .labels > div .content .org { text-transform: uppercase; }
body.labels .labels > div .content .tels,
body.labels .labels > div .content .email,
body.labels .labels > div .content .pro { font-size: 9px; }

<?php echo $params['free-css'] ?>
