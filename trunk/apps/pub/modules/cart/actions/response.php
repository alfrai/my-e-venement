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
  $bank->code = $request->getParameter('error');
  $bank->payment_certificate = $request->getParameter('signature');
  $bank->authorization_id = $request->getParameter('authorization');
  $bank->merchant_id = $request->getParameter('paybox_id');
  $bank->customer_ip_address = $request->getParameter('ip_country');
  $bank->capture_mode = $request->getParameter('card_type');
  $bank->transaction_id = $request->getParameter('transaction_id');
  $bank->amount = $request->getParameter('amount');
  $bank->raw = $_SERVER['QUERY_STRING'];
  
  try {
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
  $bank->save();
  
  $payment = new Payment;
  $payment->sf_guard_user_id = $this->getUser()->getId();
  $payment->payment_method_id = sfConfig::get('app_tickets_payment_method_id');
  $payment->value = $bank->amount/100;
  
  $this->getUser()->setAttribute('transaction_id',$bank->transaction_id);
  $this->getCurrentTransaction()->Contact->confirmed = true;
  $this->getCurrentTransaction()->Payments[] = $payment;
  $this->getCurrentTransaction()->Orders[] = new Order;
  $this->getCurrentTransaction()->save();
  
  return sfView::NONE;
