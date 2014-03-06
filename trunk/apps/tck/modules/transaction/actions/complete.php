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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  /**
   * function executeComplete()
   * @param sfWebRequest $request
   * @return ''
   * @display a JSON array containing
   * error:
   *   0: boolean true if errorful, false else
   *   1: string explanation
   * success:
   *   success_fields:
   *     [FIELD_NAME]:
   *       data:
   *         type: string
   *         reset: boolean
   *         content: mixed DATA
   *       remote_content:
   *         url: string url to GET after recieved this response
   *         text: string
   *         load:
   *           target:  string the target of this result (to be deprecated?)
   *           type:    string the type of result
   *           data:    mixed
   *           reset:   boolean
   *           default: mixed default value
   *   error_fields:
   *     [FIELD_NAME]: string explanation
   *
   **/

    // prepare response
    $this->json = array(
      'error' => array(false, ''),
      'success' => array(
        'success_fields' => array(),
        'error_fields'   => array(),
      ),
      'base_model' => 'transaction',
    );
    
    // get back data
    $params = $request->getParameter('transaction',array());
    if (!( is_array($params) && count($params) > 0 ))
    {
      $this->json['error'] = array('true', 'The given data is incorrect');
      return;
    }
    
    // embedded data
    if ( count($params) == 1 )
    {
      $v = array_values($params);
      $params['_csrf_token'] = $v[0]['_csrf_token'];
    }
    
    // csrf token
    if ( !isset($params['_csrf_token']) )
    {
      $this->json['error'] = array(true, 'No CSRF tocken given.');
      return;
    }
    
    $success = array(
      'data' => array(),
      'remote_content' => array(
        'url'   => '',
        'text'  => '',
        'load'  => array(
          'target' => NULL,
          'type'   => NULL,
          'data'   => NULL,
          'reset'  => true,
          'default'=> NULL,
        ),
      ),
    );
    
    // direct transaction's fields
    foreach ( array('contact_id', 'professional_id', 'description',) as $field )
    if ( isset($params[$field]) && isset($this->form[$field]) )
    {
      $this->json['success']['success_fields'][$field] = $success;
      $this->json['success']['success_fields'][$field]['data'] = $params[$field];
      $this->form[$field]->bind(array($field => $params[$field], '_csrf_token' => $params['_csrf_token']));
      if ( $this->form[$field]->isValid() )
      {
        // data to bring back
        switch($field) {
        case 'contact_id':
          $this->json['success']['success_fields'][$field]['remote_content']['load']['target'] = '#li_transaction_field_professional_id select:first';
          $this->json['success']['success_fields'][$field]['remote_content']['load']['type']   = 'options';
          
          if ( $params[$field] )
          {
            $object = Doctrine::getTable('Contact')->findOneById($params[$field]);
            foreach ( $object->Professionals as $pro )
              $this->json['success']['success_fields'][$field]['remote_content']['load']['data'][$pro->id]
                = $pro->full_desc;
            $this->json['success']['success_fields'][$field]['remote_content']['load']['default'] = $this->transaction->professional_id;
            
            $this->json['success']['success_fields'][$field]['remote_content']['url']  = cross_app_url_for('rp', 'contact/show?id='.$params[$field], true);
            $this->json['success']['success_fields'][$field]['remote_content']['text'] = (string)$object;
          }
          break;
        }
        
        $this->transaction->$field = $params[$field] ? $params[$field] : NULL;
        $this->transaction->save();
      }
      else
      {
        $this->json['success']['error_fields'][$field] = (string)$this->form[$field]->getErrorSchema();
      }
    }
    
    // more complex data
    foreach ( array('price_new', 'payment_new', 'payments_list', 'close') as $field )
    if ( isset($params[$field]) && is_array($params[$field]) && isset($this->form[$field]) )
    {
      $this->json['success']['success_fields'][$field] = $success;
      
      $this->form[$field]->bind($params[$field]);
      if ( $this->form[$field]->isValid() )
      switch ( $field ) {
      case 'price_new':
        if ( !$params[$field]['qty'] )
          $params[$field]['qty'] = 1;
        
        // preparing the DELETE and COUNT queries
        $q = Doctrine_Query::create()->from('Ticket tck')
          ->andWhere('tck.gauge_id = ?',$params[$field]['gauge_id'])
          ->andWhere('tck.price_id = ?',$params[$field]['price_id'])
          ->andWhere('tck.transaction_id = ?',$request->getParameter('id'))
          ->andWhere('tck.printed_at IS NULL')
          ->orderBy('tck.integrated_at IS NULL DESC, tck.integrated_at, tck.numerotation IS NULL DESC, id DESC');
        
        $state = 'false';
        if ( isset($params[$field]['state']) && $params[$field]['state'] == 'integrated' )
        {
          $state = 'integrated';
          $q->andWhere('tck.integrated_at IS NOT NULL');
        }
        else
          $q->andWhere('tck.integrated_at IS NULL');
        
        $this->json['success']['success_fields'][$field]['data'] = array(
          'type'  => 'gauge_price',
          'reset' => true,
          'content' => array(
            'qty'   => $q->count() + $params[$field]['qty'],
            'price_id'  => $params[$field]['price_id'],
            'gauge_id'  => $params[$field]['gauge_id'],
            'state'   => isset($params[$field]['state']) && $params[$field]['state'] ? $params[$field]['state'] : NULL,
            'transaction_id' => $request->getParameter('id'),
          ),
        );
        
        $manifs = array();
        if ( $params[$field]['qty'] > 0 ) // add
        for ( $i = 0 ; $i < $params[$field]['qty'] ; $i++ )
        {
          $ticket = new Ticket;
          $ticket->gauge_id = $params[$field]['gauge_id'];
          $ticket->price_id = $params[$field]['price_id'];
          $ticket->transaction_id = $request->getParameter('id');
          $ticket->save();
        }
        else // delete
        {
          $q->limit(abs($params[$field]['qty']))
            ->execute()
            ->delete();
        }
        
        $this->json['success']['success_fields'][$field]['remote_content']['load']['type']
          = 'gauge_price';
        $this->json['success']['success_fields'][$field]['remote_content']['load']['url']
          = url_for('transaction/getManifestations?id='.$request->getParameter('id').'&state='.$state.'&gauge_id='.$params[$field]['gauge_id'].'&price_id='.$params[$field]['price_id'], true);
        
        break;
      case 'payment_new':
        $p = new Payment;
        $p->transaction_id = $this->transaction->id;
        $p->value = $this->form[$field]->getValue('value') ? $this->form[$field]->getValue('value') : $this->transaction->price - $this->transaction->paid;
        $p->payment_method_id = $this->form[$field]->getValue('payment_method_id');
        $p->created_at = $this->form[$field]->getValue('created_at');
        $p->save();
        
        $this->json['success']['success_fields'][$field]['remote_content']['load']['type'] = 'payments';
        $this->json['success']['success_fields'][$field]['remote_content']['load']['url']  = url_for('transaction/getPayments?id='.$request->getParameter('id'), true);
        
        break;
      case 'payments_list':
        Doctrine::getTable('Payment')
          ->findOneById($this->form[$field]->getValue('id'))
          ->delete();
        
        $this->json['success']['success_fields'][$field]['remote_content']['load']['type'] = 'payments';
        $this->json['success']['success_fields'][$field]['remote_content']['load']['url']  = url_for('transaction/getPayments?id='.$request->getParameter('id'), true);
        
        break;
      case 'close':
        $semaphore = array('products' => true, 'amount' => 0);
        foreach ( $this->transaction->Tickets as $ticket )
        {
          if ( !$ticket->printed_at && !$ticket->cancelling && !$ticket->integrated_at )
            $semaphore['products'] = false;
          else
            $semaphore['amount'] += $ticket->value;
        }
        foreach ( $this->transaction->Payments as $payment )
        {
          $semaphore['amount'] -= $payment->value;
        }
        
        if ( !$semaphore['products'] || $semaphore['amount'] != 0 )
        {
          $this->json['success']['error_fields']['close'] = $this->json['success']['success_fields']['close'];
          unset($this->json['success']['success_fields']['close']);
          
          $this->json['success']['error_fields']['close']['data']['generic'] = __('This transaction cannot be closed properly:');
          if ( !$semaphore['products'] )
            $this->json['success']['error_fields']['close']['data']['tck'] = __('Some tickets are not printed yet');
          if ( $semaphore['amount'] > 0 )
            $this->json['success']['error_fields']['close']['data']['pay'] = __('This transaction is not yet totally paid');
          if ( $semaphore['amount'] < 0 )
            $this->json['success']['error_fields']['close']['data']['pay'] = __('This transaction has more money than needed');
        }
        else
        {
          $this->transaction->closed = true;
          $this->transaction->save();
          error_log('Transaction #'.$this->transaction->id.' closed by user.');
        }
        break;
      }
      else
      {
        $this->json['success']['error_fields'][$field] = (string)$this->form[$field]->getErrorSchema();
      }
    }
    
    if ( count($this->json['success']['error_fields']) == 0 && count($this->json['success']['success_fields']) == 0 )
    {
      error_log('touchscreen: unknown request ['.implode(', ',array_keys($params)).']');
      $this->json['error'] = array(true, 'Unknown request');
    }
    
    return;

