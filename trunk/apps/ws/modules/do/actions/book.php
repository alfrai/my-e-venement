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
    *     . 400 if the payment has already been processed
    *     . 403 if authentication as a valid webservice has failed
    *     . 406 if the input json content doesn't embed the required values or contact is not registered
    *     . 409 if one of the inputed gauge will be overbooked by the current booking
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
    }
    catch ( sfSecurityException $e )
    {
      $this->getResponse()->setStatusCode('403');
      return sfView::NONE;
    }
    
    if ( ($contact_id = intval($this->getUser()->getAttribute('contact_id'))) <= 0 && sfConfig::get('app_noid') != 'true' )
    {
      $this->getResponse()->setStatusCode('406');
      return sfView::NONE;
    }
    
    // get data from booking
    try {
      $json = wsConfiguration::getData($request->getParameter('json'));
    }
    catch ( sfSecurityException $e )
    {
      $this->getResponse()->setStatusCode('412');
      return sfView::NONE;
    }
    
    // transaction
    if ( $this->getUser()->hasAttribute('transaction_id') )
      $transaction = Doctrine::getTable('Transaction')->findOneById($this->getUser()->getAttribute('transaction_id'));
    else if ( $this->getUser()->hasAttribute('old_transaction_id') )
      $transaction = Doctrine::getTable('Transaction')->findOneById($this->getUser()->getAttribute('old_transaction_id'));
    else
      $transaction = new Transaction();
    if ( $contact_id > 0 )
      $transaction->contact_id = $contact_id;
    $transaction->sf_guard_user_id = $this->getUser()->getAttribute('ws_id');
    $transaction->save();
    
    if ( $this->getUser()->hasAttribute('transaction_id') )
      $this->getUser()->setAttribute('old_transaction_id',$this->getUser()->getAttribute('transaction_id'));
    $this->getUser()->setAttribute('transaction_id',$transaction->id);
    
    if ( $transaction->Payments->count() > 0 )
    {
      $this->getResponse()->setStatusCode('400');
      return sfView::NONE;
    }
    
    // reinitializing the transaction
    $transaction->Tickets->delete();
    //$transaction->Order->delete();
    
    // adding asked tickets
    $gauges = array();
    foreach ( $json as $gauge_id => $tarifs )
    foreach ( $tarifs as $tarif => $qty )
    {
      if ( !in_array(intval($gauge_id),$gauges) )
        $gauges[] = intval($gauge_id);
      
      $gauge = Doctrine::getTable('Gauge')->findOneById($gauge_id);
      
      // out of gauge
      if ( $gauge->getFree(sfConfig::get('project_tickets_count_demands',false)) - $qty < 0 )
      {
        $this->getResponse()->setStatusCode('409');
        // possibility of improving the online process to point exactly on the good manifestation which has changed
        return sfView::NONE;
      }
      
      for ( $i = $qty ; $i > 0 ; $i-- )
      {
        $ticket = new Ticket();
        $ticket->manifestation_id = $gauge->manifestation_id;
        $ticket->gauge_id = $gauge_id;
        $ticket->price_name = $tarif;
        $ticket->transaction_id = $transaction->id;
        $ticket->sf_guard_user_id = $this->getUser()->getAttribute('ws_id');
        $ticket->save();
      }
    }
    
    // formatting the response
    $this->content = array(
      'transaction' => $transaction->id,
      'topay'       => $this->getWhatToPay(),
      'manifs'      => $gauges,
    );
    
    if ( !$request->hasParameter('debug') )
      $this->getResponse()->setContentType('application/json');
    
    $this->getResponse()->setStatusCode('201');
    return $request->hasParameter('debug')
      ? 'Debug'
      : $this->renderText(wsConfiguration::formatData($this->content));
