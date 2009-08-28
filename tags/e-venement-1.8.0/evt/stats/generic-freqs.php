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
	require("conf.inc.php");
	includeLib("headers");
	
	$query = " SELECT DISTINCT transaction.personneid, transaction.fctorgid
		   FROM personne, billeterie.reservation_pre AS pre, billeterie.reservation_cur AS cur, billeterie.transaction
		   WHERE cur.resa_preid = pre.id
		     AND personne.id = transaction.personneid
		     AND transaction.id = pre.transaction
		     AND NOT canceled AND NOT annul".
		     $where;
	$request = new bdRequest($bd,$query);
	$nb_spectateurs = $request->countRecords();
	$request->free();

	$query = " SELECT count(transaction.id) AS nb
		   FROM personne, billeterie.reservation_pre AS pre, billeterie.reservation_cur AS cur, billeterie.transaction
		   WHERE cur.resa_preid = pre.id
		     AND personne.id = transaction.personneid
		     AND transaction.id = pre.transaction
		     AND NOT canceled AND NOT annul".
     		     $where;
	$request = new bdRequest($bd,$query);
	$nb_billets = $request->getRecord('nb');
	$request->free();
	
	$query = " SELECT DISTINCT transaction.personneid, transaction.fctorgid, manif.evtid
		   FROM personne, billeterie.manifestation AS manif, billeterie.reservation_pre AS pre, billeterie.reservation_cur AS cur, billeterie.transaction
		   WHERE cur.resa_preid = pre.id
		     AND personne.id = transaction.personneid
		     AND transaction.id = pre.transaction
		     AND pre.manifid = manif.id
		     AND NOT canceled AND NOT annul".
		     $where;
	$request = new bdRequest($bd,$query);
	$nb_spectacles_personnes = $request->countRecords();
	$request->free();
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
  <p><?php echo htmlsecure($desc) ?></p>
	<ul>
		<li><?php echo $nb_spectateurs ?> personnes sont enregistrés en spectateurs en base</li>
		<li><?php echo $nb_billets ?> billets sont enregistrés en base</li>
		<li><?php echo $nb_spectateurs > 0 ? round($nb_billets_moy = $nb_billets/$nb_spectateurs,2) : 0 ?> billets par personne en moyenne</li>
		<li><?php echo $nb_spectateurs > 0 ? round($nb_spectacles_moy = $nb_spectacles_personnes/$nb_spectateurs,2) : 0 ?> spectacles différents par personne en moyenne</li>
		<li><?php echo $nb_spectateurs > 0 ? round($nb_spectateurs*$nb_billets/$nb_spectacles_personnes,0) : 0 ?> personnes uniques ont assisté aux spectacles (<strong>estimation</strong>, d'autant plus juste que l'écart type des moyennes est faible)</li>
	</ul>
</div>
<?php
	includeLib("footer");
?>
