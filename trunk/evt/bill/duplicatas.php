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
	require("conf.inc.php");
	includeLib('personne_properso');
	
	if ( $_GET["flashdate"] )
	$flashdate = $_GET["flashdate"];
	
	$query	= ' SELECT t.id AS transaction, pp.id, pp.nom, pp.prenom, pp.orgid, pp.orgnom, pp.fctdesc, pp.fcttype,
	                   t.accountid, a.name,
	                   SUM(nbprinted-1) AS nbprinted
	            FROM (SELECT resa_preid, count(c.resa_preid) AS nbprinted
	                  FROM reservation_cur c
	                  GROUP BY c.resa_preid) AS tmp,
	              reservation_pre p,
	              account a, transaction t
	            LEFT JOIN ('.get_personne_properso_query().') AS pp ON pp.id = t.personneid AND (pp.fctorgid = t.fctorgid OR pp.fctorgid IS NULL AND t.fctorgid IS NULL)
	            WHERE nbprinted > 1
	              AND p.id = tmp.resa_preid
	              AND t.id = p.transaction
	              AND a.id = t.accountid
	            GROUP BY t.id, p.transaction, pp.id, pp.nom, pp.prenom, pp.orgid, pp.orgnom, pp.fctdesc, pp.fcttype, t.accountid, a.name';
	$order  = ' ORDER BY transaction';
	
	$subtitle = "Rapport de duplicatas";
	
	includeLib("headers");
	
	$name_start = trim($_GET["s"]) ? trim("".htmlsecure($_GET["s"])) : "";
	$org_start = trim($_GET["o"]) ? trim("".htmlsecure($_GET["o"])) : "";
	
	if ( $name_start != '' ) $query .= " AND nom ILIKE '".$name_start."%' ";
	if ( $org_start != '' )  $query .= " AND ( orgnom ILIKE '".$org_start."%' )";
	$query .= isset($order) ? $order : " ORDER BY nom, prenom, orgnom, transaction";
	$duplicatas = new bdRequest($bd,$query);
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php") ?>
<div class="body">
<h2><?php echo $subtitle ?></h2>
<ul id="duplicatas">
	<?php
		$nbduplicatas = 0;
		while ( $rec =  $duplicatas->getRecordNext() )
		{
		  echo '<li>';
		  echo '#<a href="evt/bill/new-bill.php?t='.intval($rec['transaction']).'">'.intval($rec['transaction']).'</a> ';
		  echo '(';
		  echo ($nb = intval($rec['nbprinted'])).' duplicata'.($nb > 1 ? 's' : '');
		  $nbduplicatas += $nb;
		  echo ', <span class="resp">initiateur: '.htmlsecure($rec['name']).'</span>';
		  echo ')';
		  echo ': ';
		  echo '<a href="ann/fiche.php?id='.intval($rec['id']).'">'.htmlsecure($rec['nom'].' '.$rec['prenom']).'</a> ';
		  echo '</li>';
		}
	?>
	<li>Total: <?php echo intval($nbduplicatas) ?> duplicata<?php echo intval($nbduplicatas) > 1 ? 's' : '' ?></li>
</ul>
</div>
<?php
	$duplicatas->free();
	$bd->free();
	includeLib("footer");
?>
