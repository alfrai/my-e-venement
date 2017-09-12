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
    
    // weird cases pre-processing
    $this->form['store_integrate'] = $this->form['content']['store']->integrate;
    if ( isset($params['seat']) ) {
        $this->form['seat'] = $this->form['price_new']->seat;
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
    foreach ( array('contact_id', 'professional_id', 'postalcode', 'country', 'description', 'deposit', 'with_shipment') as $form )
    if ( isset($params[$form]) && (isset($this->form[$form]) || $this->form['more']->getWidgetSchema()->offsetExists($form)) )
    {
      $field = $form;
      if ( !isset($this->form[$form]) )
        $form = 'more';
      
      $this->json['success']['success_fields'][$field] = $success;
      $this->json['success']['success_fields'][$field]['data'] = $params[$field];
      $this->form[$form]->bind(array($field => $params[$field], '_csrf_token' => $params['_csrf_token']));
      if ( $this->form[$form]->isValid() )
      {
        // data to bring back
        switch($field) {
        case 'contact_id':
          $this->json['success']['success_fields'][$field]['remote_content']['load']['target'] = '#li_transaction_field_professional_id select:first';
          $this->json['success']['success_fields'][$field]['remote_content']['load']['type']   = 'options';
          
          if ( $params[$field] )
          {
            $object = Doctrine::getTable('Contact')->findOneById($params[$field]);
            $this->transaction->postalcode = '';
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
    foreach ( array('price_new', 'seat', 'payment_new', 'payments_list', 'store_integrate', 'close', 'gift_coupon') as $field )
    if ( isset($params[$field]) && is_array($params[$field]) && isset($this->form[$field]) )
    {
      $this->json['success']['success_fields'][$field] = $success;
      
      // pre-processing
      switch ( $field ) {
      case 'price_new':
        foreach ( array('pdt-declination' => 'declination') as $orig => $real )
        if ( $params[$field]['type'] == $orig )
          $params[$field]['type'] = $real;
        
        // security
        $security = array('declination' => 'tck-pos');
        if ( isset($security[$params[$field]['type']]) && !$this->getUser()->hasCredential($security[$params[$field]['type']]) )
          break;
        
        $q = Doctrine_Query::create();
        $model = NULL;
        switch ( $params[$field]['type'] ) {
        case 'declination':
          $model = 'ProductDeclination';
          $q->from($model.' d')
            ->leftJoin('d.Product p')
            ->andWhereIn('p.meta_event_id IS NULL OR p.meta_event_id', array_keys($this->getUser()->getMetaEventsCredentials()))
          ;
          break;
        //default:
        case 'gauge':
          $model = 'Gauge';
          $q->from($model.' g')
            ->leftJoin('g.Manifestation m')
            ->leftJoin('m.Event e')
            ->andWhereIn('e.meta_event_id', array_keys($this->getUser()->getMetaEventsCredentials()))
            ->andWhereIn('g.workspace_id', array_keys($this->getUser()->getWorkspacesCredentials()))
          ;
          break;
        }
        
        $vs = $this->form[$field]->getValidatorSchema();
        $vs['declination_id'] = new sfValidatorDoctrineChoice(array(
          'model' => $model,
          'query' => $q,
        ));
        
        // for WIPs
        if ( intval($params[$field]['price_id']).'' !== ''.$params[$field]['price_id'] )
          $params[$field]['price_id'] = NULL;
        break;
      }
      
      // processing
      $this->form[$field]->bind($params[$field]);
      
      // post-processing
      if ( $this->form[$field]->isValid() )
      switch ( $field ) {
      case 'seat':
        $gid = $params[$field]['gauge_id'];
        $qty = $params[$field]['qty'];
        $seater = new Seater($gid);
        $seats = $seater->findSeats($qty);
        
        $q = Doctrine::getTable('Ticket')->createQuery('tck')
            ->andWhere('tck.gauge_id = ?', $gid)
            ->andWhere('tck.seat_id IS NULL')
            ->andWhere('tck.transaction_id = ?', $params[$field]['id'])
        ;
        $tickets = $q->execute();
        $res = array();
        for ( $i = 0 ; $i < $tickets->count() && $i < $qty && $i < $seats->count() ; $i++ ) {
            $seat_keys = $seats->getKeys();
            $tickets[$i]->Seat = $seats[$seat_keys[$i]];
            $res[] = $tickets[$i]->toArray(false) + array('seat_name' => $tickets[$i]->Seat->name);
            $tickets[$i]->save();
        }
        
        $this->json['success']['success_fields']['seat']['data']['tickets'] = $res;
        $this->json['success']['success_fields']['seat']['data']['type'] = 'seat';
        break;
      case 'price_new':
        if ( !$params[$field]['qty'] )
          $params[$field]['qty'] = 1;
        
        require(__DIR__.'/complete-price-new.php');
        break;
      case 'payment_new':
        try {
          $p = new Payment;
          $p->transaction_id = $this->transaction->id;
          $p->value = $this->form[$field]->getValue('value')
            ? $this->form[$field]->getValue('value')
            : $this->transaction->price - $this->transaction->paid;
          $p->payment_method_id = $this->form[$field]->getValue('payment_method_id');
          $p->created_at = $this->form[$field]->getValue('created_at');
          $p->detail = trim($this->form[$field]->getValue('detail')) ? trim($this->form[$field]->getValue('detail')) : NULL;
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
            ->andWhere('(SELECT SUM(pp.value) FROM Payment pp WHERE mc.id = pp.member_card_id) < 0')
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
      case 'store_integrate':
        $this->json['success']['success_fields'][$field] = $success;
        $force = $this->form[$field]->getValue('force') && $this->getUser()->hasCredential('tck-admin');
        $error_stock = 0;
        $products = new Doctrine_Collection('BoughtProduct');
        foreach ( Doctrine::getTable('BoughtProduct')->createQuery('bp')
          ->andWhere('bp.transaction_id = ?', $this->form[$field]->getValue('id'))
          ->andWhere('bp.integrated_at IS NULL')
          ->leftJoin('bp.Transaction t')
          ->leftJoin('bp.Declination d')
          ->execute() as $bp )
        {
          if ( $bp->product_declination_id && ($bp->Declination->stock > 0 || $force || $bp->destocked) )
          {
            $bp->integrated_at = date('Y-m-d H:i:s');
            $bp->save();
            $products[] = $bp;
          }
          else
            $error_stock++;
        }
        
        if ( $products->count() > 0 )
          $this->dispatcher->notify(new sfEvent($this, 'tck.products_integrate', array(
            'transaction' => $products[0]->Transaction,
            'products'    => $products,
            'duplicate'   => false,
            'user'        => $this->getUser(),
          )));
        
        // MemberCard created
        $mc_conf = sfConfig::get('app_transaction_membercard', array('integrate' => false));
        if ( $bp->member_card_id && $mc_conf['integrate'] )
        {
          $this->json['success']['success_fields']['member_card']['remote_content']['load']['type'] = 'member_card';
          $this->json['success']['success_fields']['member_card']['remote_content']['load']['data']['member_card_type_id'] = $bp->MemberCard->member_card_type_id;
        }
        
        if ( $error_stock > 0 )
          $this->json['error'] = array(
            true,
            format_number_choice(
              '[1]One product cannot be delivered, its stock is empty.'.
              '|'.
              '(1,+Inf]%%nb%% products cannot be delivered, their stocks are empty.',
              array('%%nb%%' => $error_stock),
              $error_stock
            ),
            __('Do you want to force the delivery?'),
            $field,
            'force',
          );
        $this->json['success']['success_fields'][$field]['remote_content']['load']['type']  = 'store_price';
        $this->json['success']['success_fields'][$field]['remote_content']['load']['url']   = url_for('transaction/getStore?id='.$request->getParameter('id'), true);
        break;
      case 'close':
        $items = $this->transaction->getItemables();
        $semaphore = array('products' => true, 'amount' => 0);
        
        $this->dispatcher->notify(new sfEvent($this, 'tck.before_trying_to_close_transaction', array(
          'transaction' => $this->transaction,
          'user'        => $this->getUser(),
        )));
        
        foreach ( $items as $pdt )
        if ( !$pdt->isSold() )
        {
          $semaphore['products'] = false;
          break;
        }
        
        if ( !( $semaphore['products'] || !sfConfig::get('app_tickets_alert_on_notprinted', true) && $this->transaction->Order->count() > 0 )
          || ($semaphore['amount'] = $this->transaction->getPaid() - $this->transaction->getPrice(true,true)) != 0 )
        {
          $this->json['success']['error_fields']['close'] = $this->json['success']['success_fields']['close'];
          unset($this->json['success']['success_fields']['close']);
          
          $this->json['success']['error_fields']['close']['data']['generic'] = __('This transaction cannot be closed properly:');
          if ( !$semaphore['products'] )
            $this->json['success']['error_fields']['close']['data']['pdt'] = __('Some products are not sold (printed?) yet');
          if ( $semaphore['amount'] < 0 )
            $this->json['success']['error_fields']['close']['data']['pay'] = __('This transaction is not yet totally paid');
          if ( $semaphore['amount'] > 0 )
            $this->json['success']['error_fields']['close']['data']['pay'] = __('This transaction has more money than needed');
        }
        elseif ( $semaphore['products'] ) // closing transaction
        {
          $this->transaction->closed = true;
          error_log('Transaction #'.$this->transaction->id.' closed by user.');
        }
        
        if ( $this->transaction->isModified() )
          $this->transaction->save(); // saving the transaction even if nothing has changed, because of the dispatcher's actions
        break;
      
      case 'gift_coupon':
        $id = null;
        $code = $this->form[$field]->getValue('code');

        if ( intval('9'.$code).'' !== '9'.$code )
        {
          if ( $data = json_decode($code, true) )
          {
            if ( $data['type'] == 'MemberCard' )
            {
              $id = (int)$data['member_card_id'];
            }
          }
        } 
        else
        {
          $id = intval($code);
        }

        if ($id) {
          $mc = Doctrine::getTable('MemberCard')->find($id);
          $mc->Transaction = $this->transaction;
          $mc->save();
          $this->json['success']['success_fields'][$field]['data']['type'] = $field;
          $this->json['success']['success_fields'][$field]['data']['id'] = $id;
          $this->json['success']['success_fields'][$field]['data']['name'] = $mc->name;
          $this->json['success']['success_fields'][$field]['data']['alert'] = __('Gift coupon #%%mc%% successfully added to the current transaction.', array('%%mc%%' => $id));
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

