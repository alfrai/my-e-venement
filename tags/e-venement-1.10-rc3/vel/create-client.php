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
    * pre-login as a client
    * (distinct from the real "authentication" of the distant system, that's why we told it 'identification'
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    * POST params:
    *   - json : a json array containing all the information needed (note that the password has to be sent already md5'ized
    * Returns :
    *   - HTTP return code
    *     . 500 if there was a problem processing the demand
    *     . 403 if authentication as a valid webservice has failed
    *     . 412 if all the required arguments have not been sent
    *     . 201 if a new client account has been created
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  $nav->mimeType('text/plain');
  
  // general auth
  if ( !$auth )
  {
    $nav->httpStatus(403);
    die();
  }

  // preconditions
  $json = json_decode($_POST['json'],true);
  if ( isset($_GET['debug']) )
    print_r($json);
  
  if ( !verifyChecksum($json,$salt) )
  {
    $nav->httpStatus(412);
    die();
  }
  $client = freeChecksum($json);
  if ( !$client['email'] || !$client['lastname'] || !$client['firstname'] || !$client['password'] )
  {
    $nav->httpStatus(412);
    die();
  }
  
  $fields = array(
    'lastname'  => 'nom',
    'firstname' => 'prenom',
    'email'     => 'email',
    'password'  => 'password',
    'address'   => 'adresse',
    'postal'    => 'cp',
    'city'      => 'ville',
    'telephone' => 'telephone',
  );
  
  $rec = array();
  foreach ( $fields as $from => $to )
    $rec[$to] = $client[$from];
  unset($rec['telephone']);
  $rec['ville'] = strtoupper($rec['ville']);
  $rec['active'] = 'f';
  
  if ( $bd->addRecord('personne',$rec) !== false )
  {
    $client['id'] = $bd->getLastSerial('entite','id');
    $bd->addRecord('telephone_personne',array('entiteid' => $client['id'], 'numero' => $client['telephone']));
    $nav->httpStatus(201);
    die();
  }
  
  header('HTTP/1.1 500 Internal Server Error');
  die();
?>
