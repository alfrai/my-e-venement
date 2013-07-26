<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
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

    if ( !$request->hasParameter('debug') )
      $this->getResponse()->setContentType('application/json');
    
    try { $this->authenticate($request); }
    catch ( sfException $e )
    {
      $this->getResponse()->setStatusCode('403');
      return $request->hasParameter('debug') ? 'Debug' : sfView::NONE;
    }
    
    if ( ($tid = $this->getUser()->hasAttribute('transaction_id') ? $this->getUser()->getAttribute('transaction_id') : $this->getUser()->getAttribute('old_transaction_id')) )
    {
      $t = array('transaction' => $tid);
      return $request->hasParameter('debug') ? 'Debug' : $this->renderText(wsConfiguration::formatData(array('transaction' => $tid)));
    }
    
    $this->getResponse()->setStatusCode('500');
    return 'Error';
?>
