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
    * create a totally new account for the current client
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    * Returns :
    *   - HTTP return code
    *     . 200 if a new client account has been created
    *     . 403 if authentication as a valid webservice has failed
    *     . 500 if there was a problem processing the demand
    *   - json: the current transaction number in a JSON way
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  session_start();
  $nav->mimeType(isset($_GET['debug']) ? 'text/plain' : 'application/json');
  
  // general auth
  if ( !$auth )
  {
    $nav->httpStatus(403);
    die();
  }
  
  if ( $tid = intval($_SESSION['transaction'] ? $_SESSION['transaction'] : $_SESSION['oldtransaction']) )
  {
    $nav->httpStatus(200);
    $t = array('transaction' => $tid);
    echo addChecksum($t,$salt);
    die();
  }
  
  $nav->httpStatus(500);
  die();
?>

