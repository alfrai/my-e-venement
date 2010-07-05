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
    *   - bdc     : if '0' : remove the pre-reservation "flag" ; if '1' : put the "pre-reservation" flag
    * Returns :
    *   -   0 : ok, no problem, updating the DB has been going good
    *   -   1 : error in the DB updating, like connection problem, or query error (eg: bdc exists already)
    *   -   2 : error, transac given was messed up
    *   - 254 : error in user's rights
    *   - 255 : misc error in data treatment 
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  if ( $user->evtlevel < $config["evt"]["right"]["mod"] )
  {
    echo '254';
    beta_die(254);
  }
  
  $transac = intval($_GET['transac']);
  $bdc = intval($_GET['bdc']);
  
  if ( $transac > 0 )
  {
    switch ( $bdc ) {
      case 1:
        $r = $bd->addRecord('bdc',array('transaction' => $transac, 'accountid' => $user->getId()));
        if ( $r === false )
          $die = 1;
        elseif ( $r != 1 )
          $die = 255;
        else
          $die = 0;
        break;
      case 0:
      default:
        $r = $bd->delRecordsSimple('bdc',array('transaction' => $transac));
        if ( $r === false )
          $die = 1;
        elseif ( $r != 1 )
          $die = 255;
        else
          $die = 0;
        break;
    }
  }
  else
    $die = 2;
  
  echo $die;
  die($die);
?>
