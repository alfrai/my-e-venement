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
    * initiates the transaction, prereserving the tickets before paiement
    * don't forget the HTTP session given after identifying the client
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    * POST params: a var "json" containing this kind of json content
    *   - json: a json array describing the command (see in the code for sample)
    * Returns :
    *   - HTTP return code
    *     . 201 if tickets have been well pre-reserved
    *     . 403 if authentication as a valid webservice has failed
    *     . 406 if the input json content doesn't embed the required values
    *     . 412 if the input json array is not conform with its checksum
    *     . 500 if there was a problem processing the demand
    *   - a json array containing :
    *     . manifestations quoted for updates
    *     . required amount to pay
    *     . transaction id to give back for paiement and final reservation
    *
    **/

    try {
      $this->authenticate($request);
      if ( ($contact_id = intval($this->getUser->getParameter('contact_id')) <= 0) )
        throw new sfSecurityException();
    }
    catch ( sfSecurityException $e )
    {
      $this->getResponse()->setStatusCode('403');
      return sfView::NONE;
    }
    
    // get data from booking
    try {
      $json = wsConfiguration::getData($request->getParameters('json'));
    }
    catch ( sfSecurityException $e )
    {
      $this->getResponse()->setStatusCode('412');
      return sfView::NONE;
    }
    
    // new transaction
    $transaction = new Transaction();
    $transaction->contact_id = $contact_id;
    $transaction->save();
    
    // adding asked tickets
    $manifs = array();
    foreach ( $json as $manifid => $tarifs )
    foreach ( $tarifs as $tarif => $qty )
    {
      if ( !in_array(intval($manifid),$manifs) )
        $manifs[] = intval($manifid);
      
      for ( $i = $qty ; $i > 0 ; $i-- )
      {
        $ticket = new Ticket();
        $ticket->manifestation_id = $manifid;
        $ticket->price_name = $tarif;
        $ticket->transaction_id = $transaction->id;
        $ticket->save();
      }
    }
    
    // formatting the response
    $infos = array(
      'transaction' => $transaction->id,
      'topay'       => $this->getWhatToPay(),
      'manifs'      => $manifs,
    );
    
    if ( !$request->hasParameter('debug') )
      $this->getResponse()->setContentType('application/json');
    
    return $request->hasParameter('debug')
      ? 'Debug'
      : $this->renderText(wsConfiguration::formatData($this->content));
