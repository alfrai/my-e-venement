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
    * Permit remembering the events selected
    * GET params :
    *   - manifs  : an array of manifestations' ids (if empty, empties the memory)
    * Returns :
    *   -   0 : ok, the ids were memorized
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  $manifs = $_GET['manifs'];
  print_r($manifs);
  
  if ( is_array($manifs) )
  {
    foreach ( $manifs as $key => $id )
      $manifs[$key] = intval($id);
    
    $_SESSION["evt"]["express"]["manif"] = $manifs;
  }
  else
    unset($_SESSION["evt"]["express"]["manif"]);
  
  echo 0;
  $bd->free();
  die(0);
?>

