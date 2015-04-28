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
  if (!(
     class_exists($class = ucfirst($plugin = sfConfig::get('app_payment_type','paybox')).'Payment')
  && is_a($class, 'OnlinePaymentInterface', true)
  ))
    throw new liOnlineSaleException('You asked for a payment plugin ('.$plugin.') that does not exist.');
  
  $transaction = Doctrine::getTable('Transaction')->findOneById($class::getTransactionIdByResponse($request));
  $this->getUser()->setTransaction($transaction); // linking the transaction to the current session/user
  $cultures = sfConfig::get('project_internals_cultures', array('fr' => 'Français'));
  $this->getUser()->setCulture($culture = $transaction->Contact->culture
    ? $transaction->Contact->culture
    : array_shift(array_keys($cultures))
  );
  $this->online_payment = $class::create($transaction);
  
  // records a BankPayment Record and valid (or not)
  $r = $this->online_payment->response($request);
  if ( !$r['success'] )
    throw new liOnlineSaleException('An error occurred during the bank verifications');
  
  if ( $transaction->getPaid().'' >= ''.$transaction->getPrice(true, true) ) // this .'' is a hack for precise float values
    return sfView::NONE;
  
  // direct payment
  $payment = new Payment;
  $payment->sf_guard_user_id = $this->getUser()->getId();
  $payment->payment_method_id = sfConfig::get('app_tickets_payment_method_id');
  $payment->value = $r['amount'];
  if ( method_exists($this->online_payment, 'getProviderTransactionId')
    && $this->online_payment->getProviderTransactionId() )
    $payment->detail = $this->online_payment->getProviderTransactionId();
  
  if ( $mc_pm = $this->getMemberCardPaymentMethod() )
  {
    // payments linked to member cards' credit
    foreach ( $transaction->MemberCards as $mc )
    {
      $mc->contact_id = $transaction->contact_id;
      $p = new Payment;
      $p->Transaction = $transaction;
      $p->value = -$mc->MemberCardType->value;
      $p->Method = $mc_pm;
      $mc->Payments[] = $p;
    }
    
    // payments done by member card
    $this->createPaymentsDoneByMemberCards($mc_pm);
  }
  
  // activate member cards linked to this transaction
  foreach ( $transaction->MemberCards as $mc )
    $mc->active = true;
  
  $transaction->Contact->confirmed = true;        // transaction's contact
  foreach ( $transaction->Tickets as $ticket )    // for "named" tickets
  if ( $ticket->contact_id )
    $ticket->DirectContact->confirmed = true;
  $transaction->Payments[] = $payment;
  if ( $transaction->Order->count() == 0 )
    $transaction->Order[] = new Order;
  $transaction->save();

  if ( $this->online_payment->BankPayment instanceof BankPayment )
  {
    $this->online_payment->BankPayment->payment_id = $payment;
    $this->online_payment->BankPayment->save();
  }
  
  // sending emails to contact and organizators
  $this->sendConfirmationEmails($transaction, $this);
  
  return sfView::NONE;

