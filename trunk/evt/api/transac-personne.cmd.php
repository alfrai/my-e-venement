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
    *   - client  : string like prof_940 where 940 is the personne_properso.fctorgid, for professional clients
    *               string like pers_2094 where 2094 is the personne_properso.id, for individual clients
    *   - transac : transaction's number, as given by the software
    * Returns :
    *   -   0 : ok, no problem, updating the DB has been going good
    *   -   1 : error in the DB updating, like connection problem, or query error
    *   -   2 : error, transac or client given were messed up
    *   - 255 : error, not just 1 record has been updated (0 or more than 1)
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  // on ajoute un client à la transaction
  if ( intval(substr($_GET['client'],5)) > 0 && intval($_GET['transac']) > 0 )
  {
    $personne = array();
    
    if ( substr($_GET['client'],0,4) == 'prof' )
    {
      $personne['fctorgid'] = intval(substr($_GET['client'],5));
      $query = ' SELECT id FROM personne_properso WHERE fctorgid = '.$personne['fctorgid'];
      $request = new bdRequest($bd,$query);
      $personne['personneid'] = intval($request->getRecord('id'));
      $request->free();
    }
    else
      $personne['personneid'] = intval(substr($_GET['client'],5));
    
    $r = $bd->updateRecordsSimple('transaction',
      array('id' => intval($_GET['transac'])),
      $personne);
    
    $bd->free();
    
    if ( ($r = intval($r)) === 1 )
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
  else
  {
    echo 2;
    die(2);
    $bd->free();
  }
?>
