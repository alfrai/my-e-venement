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
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    *   - email: the client's email (required)
    *   - name: the client's name (required)
    *   - passwd: the client md5(passwd) (already encrypted)
    *   - request: just set this GET var to send a new password to the client's email if existing in database
    * Returns :
    *   - HTTP return code
    *     . 500 if there was a problem processing the demand
    *     . 401 if passwd/email are invalid or if authentication as a valid webservice has failed
    *     . 404 if there is no such email in database when asked for a new password
    *     . 412 if all the required arguments have not been sent
    *     . 201 if a new password has been created
    *     . 202 if all is ok, the authentication worked
    *   - json: if necessary (new password), this is returned in a JSON way
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  if ( !isset($_GET['debug']) )
    $nav->mimeType('application/json');
  else
    $nav->mimeType('text/plain');
  
  // general auth
  if ( !$auth )
  {
    $nav->httpStatus(401);
    die();
  }

  // preconditions
  if ( !$_GET['email'] && !$_GET['name'] )
  {
    $nav->httpStatus(412);
    die();
  }
  
  // changing password
  if ( isset($_GET['request']) )
  {
    includeLib('getpwd');
    if ( ($r = $bd->updateRecordsSimple(
        'personne',
        array('lower(email)'    => strtolower($_GET['email']),
              'lower(nom)' => strtolower($_GET['name'])),
        array('password' => md5($passwd = getNewPasswd()))
      )) !== false )
    {
      if ( $r > 0 )
      {
        $nav->httpStatus(201);
        echo addChecksum(array('password' => $passwd),$salt);
      }
      else
        $nav->httpStatus(404);
    }
    else
      $nav->httpStatus(500);
    die();
  }
  
  // identification
  if ( $_GET['passwd'] )
  {
    $where = array(
      'lower(email)'  => strtolower($_GET['email']),
      'lower(nom)'    => strtolower($_GET['name']),
      'password'      => md5($_GET['passwd']),
    );
    foreach ( $where as $key => $value )
      $where[$key] = $key." = '".pg_escape_string($value)."'";
    $query = 'SELECT count(*) > 0 AS identification
              FROM personne
              WHERE '.implode(' AND ',$where);
    $request = new bdRequest($bd,$query);
    $id = $request->getRecord('identification') == 't';
    $request->free();
    
    if ( $id )
    {
      session_start();
      $_SESSION['personneid'] = $id;
      $nav->httpStatus(202);
    }
    else
      $nav->httpStatus(401);
    die();
  }

  header('HTTP/1.1 500 Internal Server Error');
  die();
?>
