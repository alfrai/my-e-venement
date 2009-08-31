<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    beta-libs is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with beta-libs; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
  require("conf.inc.php");
  $css[] = 'evt/styles/new-bill.css';
  $class .= ' evt finish';
  
  if ( $user->evtlevel < $config["evt"]["right"]["mod"] )
  {
    $user->addAlert($msg = "Vous n'avez pas un niveau de droits suffisant pour accéder à cette fonctionnalité");
    $nav->redirect($config["website"]["base"]."evt/bill/",$msg);
  }

  includeLib("headers");
  
  $transac = intval($_POST['transac']);
  
  $query = array();
  $query[] = ' SELECT sum(montant) AS paid
               FROM   paiement
               WHERE  transaction = '.$transac;
  
  $query[] = ' SELECT sum(prix) AS topay
               FROM reservation_pre p, reservation_cur c,
                    (
                      SELECT id, manifid, prix FROM tarif_manif tm WHERE prixspec IS NULL
                      UNION
                      SELECT id, manifid, prixspec AS prix FROM tarif_manif tm WHERE prixspec IS NOT NULL
                    ) AS tm
               WHERE p.id = c.resa_preid
                 AND tm.manifid = p.manifid
                 AND tm.id = p.tarifid
                 AND NOT c.canceled
                 AND transaction = '.$transac;
  $request = new bdRequest($bd,'SELECT ('.$query[0].') AS paid, ('.$query[1].') AS topay');
  if ( $request->getRecord('topay') > $request->getRecord('paid') )
  {
    $user->addAlert("Erreur lors de la validation finale de l'opération...");
    $nav->redirect('evt/bill/new-bill.php?t='.$transac);
  }
  $request->free();
  
  $query = ' SELECT p.*
             FROM personne_properso p, transaction t
             WHERE t.id = '.$transac.'
               AND p.id = t.personneid
               AND ( p.fctorgid = t.fctorgid OR p.fctorgid IS NULL AND t.fctorgid IS NULL )';
  $request = new bdRequest($bd,$query);
  $client = $request->getRecord();
  $request->free();
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require(dirname(__FILE__).'/actions.php'); ?>
<div class="body">
<?php if ( $subtitle ) echo htmlsecure($subtitle); ?>
<p class="numtransac"><span>Numéro d'opération:</span> <span>#<?php echo htmlsecure($transac) ?></span></p>
<p class="merci">
  Merci <?php echo htmlsecure($client["titre"].' '.$client["prenom"].' '.$client["nom"]) ?>
  <a href="ann/fiche.php?id=<?php echo intval($client["id"]) ?>&view"> </a>,
  et bon(s) spectacle(s)&nbsp;!
</p>
<p class="back">&lt;&lt; <a href="evt/bill/new-bill.php">Retour</a> à l'accueil billetterie</p>
</div>
<?php
	includeLib("footer");
	$bd->free();
?>
