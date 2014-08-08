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

interface OnlinePaymentInterface
{
  /**
    * public "constructor"
    *
    * @param Transaction $transaction     the current transaction
    * @return OnlinePaymentInterface      object, ready to use
    **/
  public static function create(Transaction $transaction);
  
  /**
    * Find back the transaction id before the object is created, based on the bank response
    *
    * @param sfWebRequest $parameters     the request object made out of the bank response
    * @return integer                     the Transaction->id
    **/
  public static function getTransactionIdByResponse(sfWebRequest $parameters);
  
  /**
    * Deal with the bank response, records a BankPayment, and returns if the payment is validated or not
    *
    * @param sfWebRequest $parameters     the request object made out of the bank response
    * @return array                       array('success' => false, 'amount' => $bank->amount/100) at least
    **/
  public function response(sfWebRequest $parameters);
  
  /**
    * Renders the bank request, it can be a <form> or a <a> HTML object
    *   if you want your anchor to be directly followed, add it a "autofollow" class
    *   if you want your form to be directly submitted, add it a "autosubmit" class
    *
    * @param array                        $attributes to add to the main HTML object generated
    * @return string                      an HTML string to display as a bank request
    **/
  public function render(array $attributes = array());
  
  /**
    * Creates a BankPayment corresponding to the bank response, does not save it in the DB
    *
    * @param sfWebRequest $parameters     the request object made out of the bank response
    * @return BankPayment                 the BankPayment freshly created
    **/
  public function createBankPayment(sfWebRequest $request);
  
  /**
    * Gives a string representation of the payment request
    *   It is a standard way to call render() without a headache
    *
    * @return string                      an HTML string to display as a bank request
    **/
  public function __toString();
}
