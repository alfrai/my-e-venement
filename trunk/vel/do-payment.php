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
    * records the payment done "online"
    * don't forget the HTTP session given after identifying the client, and containing the transaction id
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    *   - paid: a string of the amount paid and validated (required)
    * Returns :
    *   - HTTP return code
    *     . 200 if payment has been well recorded and all has been paid
    *     . 201 if payment has been well recorded but it doesn't recover all the "debt"
    *     . 401 if authentication as a valid webservice has failed
    *     . 406 if the payment argument is not given
    *     . 500 if there was a problem processing the demand
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  session_start();
  $nav->mimeType(isset($_GET['debug']) ? 'text/plain' : 'application/json');
  
  // general auth
  if ( !$auth || ($pid = intval($_SESSION['personneid'])) <= 0 || ($tid = intval($_SESSION['transactionid'])) <= 0)
  {
    $nav->httpStatus(401);
    die();
  }
  
  // pre-conditions
  if ( ($paid = intval($_GET['paid'])) <= 0 )
  {
    $nav->httpStatus(406);
    die();
  }
  
  $bd->beginTransaction();
  
  // adding payment
  if ( $bd->addRecord('paiement',array(
    'transaction'     => $tid,
    //'accountid'     => $accountid,
    'modepaiementid'  => $config['vel']['payment'],
    'montant'         => $paid,
  )) !== false )
  {
    $topay = whatToPay($tid);
    if ( $topay <= 0 )
      $nav->httpStatus(200);
    else
      $nav->httpStatus(201);
    die();
  }
  
  // if all has gone crazy
  $nav->httpStatus(500);
  die();
?>
