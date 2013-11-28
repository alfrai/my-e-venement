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
    * records the payment done "online"
    * don't forget the HTTP session given after identifying the client, and containing the transaction id
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    *   - paid: a string of the amount paid and validated (required)
    * POST params :
    *   - bank: a json array with raw data from bank
    * Returns :
    *   - HTTP return code
    *     . 200 if payment has been well recorded and all has been paid
    *     . 202 if payment has been well recorded but it doesn't recover all the "debt"
    *     . 403 if authentication as a valid webservice has failed
    *     . 406 if the payment argument is false or absent
    *     . 410 if no transaction is given
    *     . 412 if payment is unrecordable
    *     . 500 if there was a problem processing the demand, including with upgrading the transaction to a pre-reservation state
    *     . 502 if there was a problem recording the raw informations coming from the bank
    *
    **/
    
    try { $this->authenticate($request); }
    catch ( sfException $e )
    {
      $this->getResponse()->setStatusCode('403');
      return sfView::NONE;
    }
    
    if ( intval($this->getUser()->getAttribute('transaction_id')) <= 0 )
    {
      $this->getResponse()->setStatusCode('410');
      return sfView::NONE;
    }
    
    if ( ($paid = floatval($request->getParameter('paid',-1))) < 0 )
    {
      $this->getResponse()->setStatusCode('406');
      return sfView::NONE;
    }
    
    if ( ($pmid = intval(sfConfig::get('app_payment_method'))) <= 0 )
    {
      $this->getResponse()->setStatusCode('500');
      return sfView::NONE;
    }
    
    // preparing the raw payment
    try { $bank = wsConfiguration::getData($request->getParameter('bank')); }
    catch ( sfException $e )
    {
      $this->getResponse()->setStatusCode('502');
      return sfView::NONE;
    }
    
    // recording the payment
    $form_payment = new PaymentForm();
    $payment = array(
      'sf_guard_user_id' => $this->getUser()->getAttribute('ws_id'),
      'transaction_id' => $this->getUser()->getAttribute('transaction_id'),
      'payment_method_id' => $pmid,
      'value' => $paid,
    );
    
    $payment[$form_payment->getCSRFFieldName()] = $form_payment->getCSRFToken();
    $form_payment->setWithUserId();
    $form_payment->bind($payment);
    if ( !$form_payment->isValid() )
    {
      $this->getResponse()->setStatusCode('412');
      return sfView::NONE;
    }
    $form_payment->save();
    $pid = $form_payment->getObject()->id;
    
    // recording datas from bank
    $form_bank = new BankPaymentForm();
    $bank['payment_id'] = $pid;
    $bank['serialized'] = serialize($bank);
    $bank['data_field'] = $bank['data'];
    unset($bank['id']);
    $bank[$form_bank->getCSRFFieldName()] = $form_bank->getCSRFToken();
    
    // removing extra fields
    $ws = $form_bank->getWidgetSchema();
    foreach ( $bank as $name => $value )
    if ( !isset($ws[$name]) )
      unset($bank[$name]);
    
    // recording data
    $form_bank->bind($bank);
    if ( $form_bank->isValid() )
      $form_bank->save();
    
    // retreiving informations about ordering / reservation
    $transaction = Doctrine::getTable('Transaction')->findOneById($this->getUser()->getAttribute('transaction_id'));
    if ( $transaction->Order->count() <= 0 )
    {
      $form_order = new OrderForm($this->Transaction->Order[0]);
      $order = array(
        'transaction_id' => $transaction->id,
        'sf_guard_user_id' => $this->getUser()->getAttribute('ws_id'),
        'type' => 'order',
        $form_order->getCSRFFieldName() => $form_order->getCSRFToken(),
      );
      $form_order->bind($order);
      if ( !$form_order->isValid() )
      {
        $this->getResponse()->setStatusCode('500');
        return sfView::NONE;
      }
      $form_order->save();
    }
    
    // last elements to build the response
    $this->getResponse()->setStatusCode( $this->getWhatToPay() > $paid ? '202' : '200' );
    $this->getUser()->setAttribute('old_transaction_id',$transaction->id);
    $this->getUser()->removeAttribute('transaction_id');
    
    return sfView::NONE;
