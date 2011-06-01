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
    * confirm a client email address
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    * POST params:
    *   - json : a json array containing:
    *     . email: the client's email (required)
    *     . firstname: the client's firstname (required)
    *     . lastname: the client's lastname (family name) (required)
    *     . code: a string constructed as email.firstname.lastname.password.salt to be verified (required)
    * Returns :
    *   - HTTP return code
    *     . 500 if there was a problem processing the demand
    *     . 403 if authentication as a valid webservice has failed
    *     . 404 if there is no such account in database corresponding to the given criterias
    *     . 412 if all the required arguments have not been sent
    *     . 201 if all went good and the account activation has worked
    *   - json: if necessary (new password), this is returned in a JSON way
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  if ( !($debug = isset($_GET['debug'])) )
    $nav->mimeType('application/json');
  else
    $nav->mimeType('text/plain');
  
  // general auth
  if ( !$auth )
  {
    $nav->httpStatus(403);
    die();
  }

  // preconditions
  $json = jsonToArray($_POST['json']);
  if ( !verifyChecksum($json,$salt) || !$json['email'] || !$json['firstname'] || !$json['lastname'] || !$json['code'] )
  {
    $nav->httpStatus(412);
    die();
  }
  $json = freeChecksum($json);
  
  $arr = array(
    'email'   => "'".pg_escape_string($json['email'])."'",
    'prenom'  => "'".pg_escape_string($json['firstname'])."'",
    'nom'     => "'".pg_escape_string($json['lastname'])."'",
    "md5(email||prenom||nom||password||'".pg_escape_string($salt)."')" => "'".pg_escape_string($json['code'])."'",
  );
  $conditions = array();
  foreach ( $conditions as $key => $value )
    $conditions = $key.' = '.$value;
  
  $r = $bd->updateRecords('personne',implode(' AND ',$conditions),array('active' => 't'));
  if ( $r && $r > 0 )
  {
    $nav->httpStatus(201);
    die();
  }
  else
  {
    $nav->httpStatus(404);
    die();
  }
  
  $nav->httpStatus(500);
  die();
?>
