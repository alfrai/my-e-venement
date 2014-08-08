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

class PayplugPayment extends OnlinePayment
{
  const name = 'payplug';
  const config_file = 'payplug.json';
  protected $value = 0;
  
  public static function create(Transaction $transaction)
  {
    self::config();
    return new self($transaction);
  }
  
  // generates the request
  public function render(array $attributes = array())
  {
    if ( !sfContext::hasInstance() )
      return (string)$this;
    
    sfContext::getInstance()->getActionStack()->getFirstEntry()->getActionInstance()->redirect($this->getUrl());
    return '';
  }
  
  public static function response(array $params = NULL)
  {
    self::config();
    $ipn = new IPN();
    return array('success' => true, 'amount' => $ipn->amount);
  }
  
  public static function completeBankRecord(BankPayment $bank, $ipn = NULL, $currency = 'EUR')
  {
    if (! $ipn instanceof IPN )
      $ipn = new IPN();
    
    // record the comparison between customData received and probably sent
    $t = new Transaction;
    $t->id                  = $ipn->order;
    $t->contact_id          = $ipn->customer;
    $t->Contact->firstname  = $ipn->firstName;
    $t->Contact->name       = $ipn->lastName;
    $t->Contact->email      = $ipn->email;
    $proof = array(
      'recieved'  => self::getMd5FromRequest(self::getRequestOptions($t, $ipn->amount, $currency)),
      'sent'      => $ipn->customData,
    );
    foreach ( $proof as $key => $value )
      $proof[$key] = "$key: $value";
    $proof = implode(' - ',$proof);
    
    // the BankPayment Record
    $bank->code                 = $ipn->state;
    $bank->payment_certificate  = $proof;
    $bank->authorization_id     = $ipn->idTransaction;
    $bank->merchant_id          = sfConfig::get('app_payment_id', 'test@test.tld');
    $bank->capture_mode         = 'payplug';
    $bank->transaction_id       = $ipn->order;
    $bank->amount               = $ipn->amount;
    $bank->raw                  = file_get_contents("php://input");
    
    return $bank;
  }
  
  protected function getUrl()
  {
    $options = $this->getRequestOptions($this->transaction, $this->value, $this->currency);
    $options['customData'] = $this->getMd5FromRequest($options);
    return PaymentUrl::generateUrl($options);
  }
  
  protected static function getRequestOptions(Transaction $transaction, $amount, $currency = 'EUR')
  {
    $config_urls = sfConfig::get('app_payment_url', array());
    foreach ( $config_urls as $key => $url )
      $config_urls[$key] = url_for($url, true);
    
    $options = array(
      'amount'    => $amount,
      'currency'  => $currency,
      'order'     => $transaction->id,
      'origin'    => 'e-voucher '.sfConfig::get('software_about_version','v2'),
      'ipnUrl'    => $config_urls['automatic'],
      'cancelUrl' => $config_urls['cancel'],
      'returnUrl' => $config_urls['normal'],
    );
    
    if ( $transaction->contact_id )
    {
      $options['customer'] = $transaction->contact_id;
      $options['firstName'] = $transaction->Contact->firstname;
      $options['lastName'] = $transaction->Contact->name;
      if ( $transaction->Contact->email )
        $options['email'] = $transaction->Contact->email;
    }
    
    return $options;
  }
  
  public static function config()
  {
    // create the specific payplug config file
    if ( !file_exists(sfConfig::get('sf_module_cache_dir').self::config_file) )
    {
      $parameters = Payplug::loadParameters(sfConfig::get('app_payment_id', 'test@test.tld'), sfConfig::get('app_payment_password', 'pass'));
      $parameters->saveInFile(sfConfig::get('sf_module_cache_dir').self::config_file);
    }
    
    // load the config file
    Payplug::setConfigFromFile(sfConfig::get('sf_module_cache_dir').self::config_file);
  }
  
  public function __toString()
  {
    return '
      <form action="'.$this->getUrl().'" method="get" class="autosubmit">
        <p><input
          type="image"
          src="https://www.payplug.fr/static/merchant/images/logo-large.png" alt="PayPlug"
          name="image"
          value=""
        /></p>
      </form>';
  }
  
  protected static function getMd5FromRequest(array $options)
  {
    return md5(json_encode($options).sfConfig::get('app_payment_salt'));
  }
}
