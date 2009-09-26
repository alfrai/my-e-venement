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
    *   - mode    : pay mode, id given by the software
    *   - amount  : how much... ;c)
    *   - date    : when will it be recorded
    *   - del     : (optional) if you want to delete this paiement
    * Returns :
    *   -   0 : ok, the paiement was recorded
    *   -   1 : error in the DB connection
    *   -   2 : error in the command calling... $_GET['transac'] not ok or others
    *   - 254 : user doesn't have the right
    *   - 255 : more than one record affected ???
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  $transac  = intval($_GET['transac']);
  $mode     = intval($_GET['mode']);
  $amount   = floatval($_GET['amount']);
  $time     = strtotime($_GET['date']);
  $date     = date('Y-m-d',$time ? $time : strtotime('now'));
  $del      = isset($_GET['del']);
  
  if ( $user->evtlevel < $config["evt"]["right"]["mod"] )
  {
    echo '254';
    die(254);
  }
  
  if ( $transac > 0 && $mode > 0 && abs($amount) > 0 )
  {
    if ( $del )
    {
      $where  = '       transaction = '.$transac.'
                    AND modepaiementid = '.$mode.'
                    AND montant = '.$amount;
      if ( $time > 0 )
        $where .= " AND date = '".$date."'";
      else
        $where .= ' AND date::date = sysdate::date';
      
      $query  = ' SELECT id
                  FROM paiement
                  WHERE '.$where.'
                  LIMIT 1';
      $request = new bdRequest($bd,$query);
      $r = $bd->delRecordsSimple('paiement',array('id' => intval($request->getRecord('id'))));
      $request->free();
      $bd->free();
    }
    else
    {
      $r = $bd->addRecord('paiement',array(
        'transaction'     => $transac,
        'modepaiementid'  => $mode,
        'montant'         => $amount,
        'date'            => $date,
      ));
      $bd->free();
    }
    
    if ( $r === false )
    {
      echo 1;
      die(1);
    }
    else if ( $r == 1 )
    {
      echo 0;
      die(0);
    }
    else
    {
      echo 255;
      die(255);
    }
  }
  
  $bd->free();
  echo 2;
  die(2);
?>

