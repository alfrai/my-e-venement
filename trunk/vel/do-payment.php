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
    *     . 202 if payment has been well recorded but it doesn't recover all the "debt"
    *     . 403 if authentication as a valid webservice has failed
    *     . 406 if the payment argument is not given
    *     . 410 if no transactionthe payment argument is not given
    *     . 500 if there was a problem processing the demand, including with upgrading the transaction to a pre-reservation state
    *     . 502 if there was a problem recording the raw informations coming from the bank
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  session_start();
  $nav->mimeType(isset($_GET['debug']) ? 'text/plain' : 'application/json');
  
  // transaction
  if ( ($tid = intval($_SESSION['transaction'])) <= 0 )
  {
    $nav->httpStatus(410);
    die();
  }
  
  // general auth
  if ( !$auth || ($pid = intval($_SESSION['personneid'])) <= 0 )
  {
    $nav->httpStatus(403);
    die();
  }
  
  // pre-conditions
  if ( ($paid = floatval($_GET['paid'])) <= 0 )
  {
    $nav->httpStatus(406);
    die();
  }
  
  $bd->beginTransaction();
  
  // adding payment
  if ( !$bd->addRecord('paiement',array(
    'transaction'     => $tid,
    //'accountid'     => $accountid,
    'modepaiementid'  => $config['vel']['payment-mode'],
    'montant'         => $paid,
  )) )
  {
    $nav->httpStatus(500);
    die();
  }
  
  // il faut ajouter l'enregistrement des différents paramètres retournés par la banque
  if ( verifyChecksum($bank = $_POST['bank'],$salt) )
  {
    $bank = freeChecksum(jsonToArray($bank));
    $serialized = serialize($bank);
    $bank['paiementid'] = $bd->getLastSerial('paiement','id');
    $bank['serialized'] = $serialized;
    if ( !$bd->addRecord('bank_payment',$bank) )
    {
      $bd->endTransaction(false);
      $nav->httpStatus(502);
      die();
    }
  }
  
  // upgrading from demands to pre-reservations
  if ( $bd->addRecord('bdc',array('transaction' => $tid, 'accountid' => $accountid)) )
  {
    $topay = whatToPay($tid);
    $nav->httpStatus($topay <= $paid ? 200 : 202);
    unset($_SESSION['transaction']);
    $bd->endTransaction();
    die();
  }
  
  // if all has gone crazy
  $bd->endTransaction(false);
  $nav->httpStatus(501);
  die();
?>
