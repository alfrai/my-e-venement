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
    *   -   0 : ok, everything has been already printed
    *   -   1 : error in the DB connection
    *   -   2 : error in the command calling... $_GET['transac'] not ok
    *   - 255 : there are still things to print...
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  $transac = intval($_GET['transac']);
  
  if ( $transac > 0 )
  {
    $query = ' SELECT count(*) AS nb
               FROM reservation_pre p
               WHERE transaction = '.$transac.'
                 AND p.id NOT IN ( SELECT resa_preid FROM reservation_cur WHERE NOT canceled )';
    $request = new bdRequest($bd,$query);
    $nb = $request->getRecord('nb');
    $request->free();
    
    $bd->free();
    
    if ( $nb === false )
    {
      echo 1;
      die(1);
    }
    elseif ( $nb == 0 )
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
