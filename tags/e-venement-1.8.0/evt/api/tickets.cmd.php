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
    *   - manifid : current manifestation's id
    *   - tarif   : tarif's key
    *   - qte     : tickets' quantity (may be negative)
    * Returns :
    *   -   0 : ok, no problem, updating the DB has been going good
    *   -   1 : error in the DB updating, like connection problem, or query error
    *   -   2 : error, transac or manifid or tarif given were messed up
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  $transac = intval($_GET['transac']);
  $manifid = intval($_GET['manifid']);
  $tarif   = $_GET['tarif'];
  $qte     = intval($_GET['qte']);
  
  if ( $transac > 0 && $manifid > 0 && $qte != 0 )
  {
    $query = " SELECT id
               FROM tarif_manif
               WHERE manifid = ".$manifid."
                 AND key ILIKE '".$tarif."'";
    $request = new bdRequest($bd,$query);
    $tarifid = $request->getRecord('id');
    $request->free();
    
    if (!(intval($tarifid) > 0))
      break;
    
    $r = 0;
    if ( $qte > 0 )
    {
      // ADD
      $data = array(
        'accountid' => $user->getId(),
        'transaction' => $transac,
        'manifid'   => $manifid,
        'tarifid'   => $tarifid,
        'reduc'     => 0,
      );
      
      for ( $i = 0 ; $i < $qte ; $i++ )
        $r += $bd->addRecord('reservation_pre',$data);
    }
    else
    {
      // REMOVE
      for ( $i = 0 ; $i < -$qte ; $i++ )
      {
        $query =  " SELECT id
                    FROM reservation_pre
                    WHERE transaction = ".$transac."
                      AND manifid = ".$manifid."
                      AND tarifid = ".$tarifid."
                      AND reduc = 0
                      AND id NOT IN ( SELECT resa_preid FROM reservation_cur WHERE NOT canceled )
                    LIMIT 1";
        $request = new bdRequest($bd,$query);
        $delid = $request->getRecord('id');
        $request->free(); 
        
        if ( intval($delid) > 0 )
          $r += $bd->delRecordsSimple('reservation_pre',array('id' => $delid));
      }
    }
    
    $bd->free();
    
    if ( ($r = intval($r)) === abs($qte) )
    {
      echo 0;
      die(0);
    }
    elseif ( $r === false )
    {
      echo 1;
      die(1);
    }
    else
    {
      echo 255;
      die(255); 
    }
  }
  
  echo 2;
  die(2);
  $bd->free();
?>
