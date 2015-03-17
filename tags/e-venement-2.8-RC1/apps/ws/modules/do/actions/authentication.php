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
    * pre-login as a client
    * (distinct from the real "authentication" of the distant system, that's why we told it 'identif$
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    *   - email: the client's email (required)
    *   - name: the client's name (required)
    *   - passwd: the client md5(passwd) (already encrypted)
    *   - request: just set this GET var to send a new password to the client's email if existing in$
    * Returns :
    *   - HTTP return code
    *     . 500 if there was a problem processing the demand
    *     . 403 if passwd/email are invalid or if authentication as a valid webservice has failed
    *     . 404 if there is no such email in database when asked for a new password
    *     . 406 if a password was given but long enough (> 4 chars)
    *     . 412 if all the required arguments have not been sent
    *     . 201 if a new password has been created
    *     . 202 if all is ok, the authentication worked
    *   - json: if necessary (new password), this is returned in a JSON way
    *
    **/
    
    $this->getResponse()->setContentType($request->hasParameter('debug')
      ? 'text/plain'
      : 'application/json');
    
    try { $this->authenticate($request); }
    catch ( sfSecurityException $e )
    {
      $this->getResponse()->setStatusCode('403');
      return sfView::NONE;
    }
    
    // preconditions
    $contact = array(
      'email' => $request->getParameter('email'),
      'name'  => $request->getParameter('name'),
    );
    if ( !$contact['email'] || !$contact['name'] )
    {
      $this->getResponse()->setStatusCode('412');
      return sfView::NONE;
    }
    
    // changing password
    // TODO
    
    // authentication
    // TODO
    
