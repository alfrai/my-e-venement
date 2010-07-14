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
    * initiates the transaction, prereserving the tickets before paiement
    * don't forget the HTTP session given after identifying the client
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    * POST params: a var "json" containing this kind of json content
    *   - json: a json array describing the command (see in the code for sample)
    * Returns :
    *   - HTTP return code
    *     . 201 if tickets have been well pre-reserved
    *     . 401 if authentication as a valid webservice has failed
    *     . 406 if the input json content doesn't embed the required values
    *     . 412 if the input json array is not conform with its checksum
    *     . 500 if there was a problem processing the demand
    *   - a json array containing :
    *     . manifestations quoted for updates
    *     . required amount to pay
    *     . transaction id to give back for paiement and final reservation
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  session_start();
  $nav->mimeType(isset($_GET['debug']) ? 'text/plain' : 'application/json');
  
  // general auth
  if ( !$auth || ($pid = intval($_SESSION['personneid'])) <= 0 )
  {
    $nav->httpStatus(401);
    die();
  }
  
  // pre-conditions
  $json = jsonToArray($_POST['json']);
  if ( !verifyChecksum($json) )
  {
    $nav->httpStatus(412);
    die();
  }
  $json = freeChecksum($json);
  
  /**
    * ex of input json array :
    * array(
    *   array(
    *     manifid => int8,
    *     tarif   => char5 (key),
    *     qty     => int8,
    *   ),
    *   [...]
    * );
    *
    **/ 
  
  $bd->beginTransaction();
  
  // new transaction
  if ( $bd->addRecord('transaction',array('accountid' => $accountid, 'personneid' => $pid)) === false )
  {
    $bd->endTransaction(false);
    $nav->httpStatus(500);
    die();
  }
  $_SESSION['transaction'] = $tid = $bd->getLastSerial('transaction','id');
  
  // adding tickets as demands
  $manifs = array();
  foreach ( $json as $tickets )
  {
    $manifs[] = intval($tickets['manifid']);
    $rec = array(
      'accountid' => $accountid,
      'manifid'   => intval($tickets['manifid']),
      'tarifid'   => "(SELECT id FROM tarif t WHERE date,key IN (SELECT max(date),key FROM tarif) AND key = '".$tickets['tarif']."')",
      'reduc'     => 0,
      'transaction' => $tid,
    );
    if ( $bd->addRecord('reservation_pre',$rec) === false )
    {
      $bd->endTransaction(false);
      $nav->httpStatus(500);
      die();
    }
  }
  
  // upgrading from demands to pre-reservations
  if ( $bd->addRecords('bdc',array('transaction' => $tid,'accountid' => $accountid)) === false )
  {
    $bd->endTransaction(false);
    $nav->httpStatus(500);
    die();
  }
  
  $bd->endTransaction();
  
  // price calculation (for returning)
  $topay = whatToPay($tid);
  
  echo addChecksum(array(
    'transaction' => $tid,
    'topay' => $topay,
    'manifs' => $manifs,
  ));
  $nav->httpStatus(201);
  die();
  
  // if all has gone crasy
  $nav->httpStatus(500);
  die();
?>
