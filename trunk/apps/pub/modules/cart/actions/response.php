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
*    Copyright (c) 2006-2012 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2012 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  $bank = new BankPayment;
  
  switch ( sfConfig::get('app_payment_type','paybox') ) {
  case 'tipi':
    try {
      TipiPayment::completeBankRecord($bank, $request);
      TipiPayment::response(array(
        'result'          => $request->getParameter('resultrans',false),
        'token'           => TipiPayment::getToken($bank->transaction_id, $bank->amount/100),
        'given_token'     => $request->getParameter('token'),
        'ip_address'      => $request->getRemoteAddress(),
        'transaction_id'  => $bank->transaction_id,
      ));
    }
    catch ( sfException $e )
    {
      $bank->error = $bank->code;
      $bank->save();
      throw $e;
    }
    
    break;
  case 'payplug':
    try {
      PayplugPayment::completeBankRecord($bank, $request);
      $r = PayplugPayment::response();
      if ( !$r['success'] )
        throw new liOnlineSaleException('An error occurred during the bank verifications');
    }
    catch ( sfException $e )
    {
      $bank->error = $bank->code;
      $bank->save();
      throw $e;
    }
    
    break;
  case 'paybox':
    try {
      PayboxPayment::completeBankRecord($bank, $request);
      $r = PayboxPayment::response($_GET);
      if ( !$r['success'] )
        throw new liOnlineSaleException('An error occurred during the bank verifications');
    }
    catch ( sfException $e )
    {
      $bank->error = $bank->code;
      $bank->save();
      throw $e;
    }
    
    break;
  }
  
  $bank->save();
  
  // direct payment
  $payment = new Payment;
  $payment->sf_guard_user_id = $this->getUser()->getId();
  $payment->payment_method_id = sfConfig::get('app_tickets_payment_method_id');
  $payment->value = $bank->amount/100;
  
  // confirm already recorded data
  $this->getUser()->setAttribute('transaction_id',$bank->transaction_id);
  $transaction = $this->getUser()->getTransaction();
  
  if ( $mc_pm = $this->getMemberCardPaymentMethod() )
  {
    // payments linked to member cards' credit
    foreach ( $transaction->MemberCards as $mc )
    {
      $mc->active = true;
      $mc->contact_id = $transaction->contact_id;
      $p = new Payment;
      $p->transaction_id = $transaction->id;
      $p->value = -$mc->MemberCardType->value;
      $p->Method = $mc_pm;
      $mc->Payments[] = $p;
    }
    
    // payments done by member card
    $this->createPaymentsDoneByMemberCards($mc_pm);
  }
  
  // contact
  $transaction->Contact->confirmed = true;
  $transaction->Payments[] = $payment;
  $transaction->Order[] = new Order;
  $transaction->save();
  
  // sending emails to contact and organizators
  $this->sendConfirmationEmails($transaction);
  
  return sfView::NONE;
