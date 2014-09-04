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
    foreach ( array('contact_id', 'professional_id', 'description', 'deposit',) as $field )
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
      
      // pre-processing
      switch ( $field ) {
      case 'price_new':
        foreach ( array('pdt-declination' => 'declination') as $orig => $real )
        if ( $params[$field]['type'] == $orig )
          $params[$field]['type'] = $real;
        
        $q = Doctrine_Query::create();
        $model = NULL;
        switch ( $params[$field]['type'] ) {
        case 'gauge':
          $model = 'Gauge';
          $q->from($model.' g')
            ->leftJoin('g.Manifestation m')
            ->leftJoin('m.Event e')
            ->andWhereIn('e.meta_event_id', array_keys($this->getUser()->getMetaEventsCredentials()))
            ->andWhereIn('g.workspace_id', array_keys($this->getUser()->getWorkspacesCredentials()))
          ;
          break;
        case 'declination':
          $model = 'ProductDeclination';
          $q->from($model.' d')
            ->leftJoin('d.Product p')
            ->andWhereIn('p.meta_event_id', array_keys($this->getUser()->getMetaEventsCredentials()))
          ;
          break;
        }
        
        $vs = $this->form[$field]->getValidatorSchema();
        $vs['declination_id'] = new sfValidatorDoctrineChoice(array(
          'model' => $model,
          'query' => $q,
        ));
        break;
      }
      
      // processing
      $this->form[$field]->bind($params[$field]);
      
      // post-processing
      if ( $this->form[$field]->isValid() )
      switch ( $field ) {
      case 'price_new':
        if ( !$params[$field]['qty'] )
          $params[$field]['qty'] = 1;
        
        require(__DIR__.'/complete-price-new.php');
        break;
      case 'payment_new':
        try {
          $p = new Payment;
          $p->transaction_id = $this->transaction->id;
          $p->value = $this->form[$field]->getValue('value') ? $this->form[$field]->getValue('value') : $this->transaction->price - $this->transaction->paid;
          $p->payment_method_id = $this->form[$field]->getValue('payment_method_id');
          $p->created_at = $this->form[$field]->getValue('created_at');
          if ( $this->form[$field]->getValue('member_card_id') )
            $p->member_card_id = $this->form[$field]->getValue('member_card_id');
          $p->save();
          $this->json['success']['success_fields'][$field]['remote_content']['load']['type'] = 'payments';
          $this->json['success']['success_fields'][$field]['remote_content']['load']['url']  = url_for('transaction/getPayments?id='.$request->getParameter('id'), true);
        }
        catch ( liMemberCardPaymentException $e )
        {
          $this->json['success']['success_fields'][$field]['data']['type'] = 'choose_mc';
          $this->json['success']['success_fields'][$field]['data']['content'] = array('payment_id' => $this->form[$field]->getValue('payment_method_id'));
          foreach ( Doctrine::getTable('MemberCard')->createQuery('mc')
            ->andWhere('mc.contact_id = ?', $this->transaction->contact_id)
            ->andWhere('mc.expire_at > NOW()')
            ->orderBy('(SELECT SUM(p.value) FROM Payment p WHERE mc.id = p.member_card_id) DESC, mc.id')
            ->execute() as $mc )
            $this->json['success']['success_fields'][$field]['data']['content'][]
              = array('id' => $mc->id, 'name' => (string)$mc);
        }
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
        foreach ( $this->transaction->getItemables() as $pdt )
        {
          if ( $pdt->isSold() )
            $semaphore['products'] = false;
          elseif ( !$pdt->isDuplicata() )
            $semaphore['amount'] += $pdt->value;
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
            $this->json['success']['error_fields']['close']['data']['pdt'] = __('Some products are not sold (printed?) yet');
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

