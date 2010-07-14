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
    * POST params: a var "json" containing this kind of json content
    *   - lastname: le nom de famille (required)
    *   - firstname: le prénom (required)
    *   - address:
    *   - postal: postal code
    *   - city:
    *   - country:
    *   - email: email (required)
    *   - tel:
    *   - passwd: the client md5(passwd) (already encrypted, required)
    * Returns :
    *   - HTTP return code
    *     . 201 if a new client account has been created
    *     . 401 if authentication as a valid webservice has failed
    *     . 406 if the json content doesn't embed the required values
    *     . 412 if the json array is not conform with its checksum
    *     . 500 if there was a problem processing the demand
    *   - json: if necessary (new password), this is returned in a JSON way
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  header('Content-Type: text/plain');
  
  // general auth
  if ( !$auth )
  {
    $nav->httpStatus(401);
    die();
  }
  
  $nav->httpStatus(500);
  die();
?>

