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
  /**
    * Specifs...
    * GET params :
    *   - transac : transaction's number, as given by the software
    * Returns :
    *   a JSON content, which reprents all the transaction's data
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  $transac  = intval($_GET['transac']);
  
  if ( $transac > 0 )
  {
    $r = array();
    
    // le client
    $request = new bdRequest($bd,' SELECT * FROM transaction WHERE id = '.$transac);
    if ( $request->countRecords() > 0 )
    {
      // customer
      $r['client']['id'] = $request->getRecord('personneid');
      $r['client']['fctorgid'] = $request->getRecord('fctorgid');
      
      // tickets
      $query  = ' SELECT p.manifid, tm.key AS tarif, tm.prix, tm.prixspec, count(*) AS nb
                  FROM reservation_pre p
                  LEFT JOIN tarif_manif tm ON tm.id = p.tarifid AND tm.manifid = p.manifid
                  LEFT JOIN reservation_cur c ON c.resa_preid = p.id AND NOT canceled
                  WHERE p.transaction = '.$transac.'
                  GROUP BY p.manifid, p.tarifid, tm.key, tm.prix, tm.prixspec
                  ORDER BY manifid, key';
      $request = new bdRequest($bd,$query);
      while ( $r['tickets'][] = $request->getRecordNext() );
      $request->free();
      array_pop($r['tickets']);
      
      // money
      $query  = ' SELECT p.*, m.libelle
                  FROM paiement p, modepaiement m
                  WHERE p.modepaiementid = m.id
                    AND transaction = '.$transac;
      $request = new bdRequest($bd,$query);
      while ( $r['paiements'][] = $request->getRecordNext() );
      $request->free();
      array_pop($r['paiements']);
      
      echo json_encode($r);
    }
    else
    {
      echo 'error';
    }
  }
  else echo 'no correct transaction id';
  $bd->free();
?>
