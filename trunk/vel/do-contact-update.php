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
    * creates or updates a contact file
    * updates a transaction with a given contact
    * only available if $config['vel']['resa-noid'] is set
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    * POST params: a var "json" containing this kind of json content
    *   - user: a json array describing the contact/user (see in the code for sample)
    *   - transaction: the transaction id concerned
    * Returns :
    *   - HTTP return code
    *     . 200 if the transaction was well updated
    *     . 403 if authentication as a valid webservice has failed
    *     . 406 if the input POST content doesn't embed the required values
    *     . 412 if the input user's json content doesn't pass its checksum
    *     . 500 if there was a problem processing the demand
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
  
  // pre-conditions
  $user = jsonToArray($_POST['user']);
  if ( !verifyChecksum($user,$salt) )
  {
    $nav->httpStatus(412);
    die();
  }
  $json = freeChecksum($user);
  
  /**
    * ex of input user array :
    * array(
    *   firstname => string,
    *   lastname => string,
    *   email => string,
    *   address => string,
    *   postal => string,
    *   city => string,
    *   [...]
    * );
    *
    **/ 
  
  $bd->beginTransaction();
  
  // check if contact exists
  $query = "SELECT * AS nb
            FROM personne
            WHERE email ILIKE '".$user['email']."'
              AND nom ILIKE '".$user['lastname']."'
              AND prenom ILIKE '".$user['firstname']."'
            ORDER BY modification DESC
            LIMIT 1";
  $request = new bdRequest($bd,$query);
  $pid = intval($request->getRecord('id'));
  
  // update or create the contact
  $arr['nom']     = $user['lastname'];
  $arr['prenom']  = $user['firstname'];
  $arr['email']   = $user['email'];
  $arr['adresse'] = $user['address'];
  $arr['cp']      = $user['postal'];
  $arr['ville']   = $user['city'];
  if ( $request->countRecords() > 0 )
    $bd->updateRecords('personne',array('id' => $pid),$arr);
  else
  {
    $bd->addRecord('personne',$arr);
    $pid = intval($bd->getLastSerial('personne','id'));
  }
  
  $request->free();
  
  // updating the transaction for the new or updated contact
  if ( $bd->updateRecordsSimple('transaction',array('id' => $user['transaction']),array('personneid' => $pid)) === false )
  {
    $nav->httpStatus(500);
    die();
  }
  
  $bd->endTransaction();
  
  $nav->httpStatus(200);
  die();
  
  // if all has gone crasy
  $nav->httpStatus(500);
  die();
?>
