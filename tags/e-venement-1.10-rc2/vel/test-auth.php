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
    * Retreiving all manifs informations sorted both by event and by site
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    * Returns :
    *   - HTTP return code
    *     . 200 if identificated
    *     . 403 if authentication as a valid webservice has failed
    *     . 500 if there was a problem processing the demand
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  // auth
  if ( !$auth )
  {
    $nav->httpStatus(403);
    die();
  }
  
  session_start();
  if ( $_SESSION['auth'] )
  {
    $nav->httpStatus(200);
    echo 'auth';
  }
  else
  {
    $nav->httpStatus(403);
    echo 'unknown';
  }
?>
