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
    * Returns :
    *   -  x : if > 0, the new transaction's number
    *   - -1 : user's rights problem
    *   - -2 : error in data treatment
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  if ( $user->evtlevel <= $config["evt"]["right"]["view"] )
  {
    echo '-1';
    beta_die(-1);
  }
  
  if ( $bd->addRecord('transaction',array('accountid' => $user->getId(), 'spaceid' => $user->evtspace ? $user->evtspace : NULL)) )
    $transac = $bd->getLastSerial('transaction','id');
  
  $bd->free();
  if ( $transac > 0 )
  {
    echo $transac;
    beta_die(0);
  }
  else
  {
    echo -2
    die(-2);
  }
?>
