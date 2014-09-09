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

class HiPayPayment extends OnlinePayment
{
  const name = 'hipay';
  protected $value = 0;
  protected $user = NULL, $config, $item, $order;
  protected $output;
  
  public static function create(Transaction $transaction)
  {
    $hipay new self($transaction);
    $hipay
      ->config()
      ->init()
    ;
    return $hipay;
  }
  
  // generates the request
  public function render(array $attributes = array())
  {
    $this->prepare();
    
    if ( !$this->url )
      return '<div class="'.$attributes['class'].'" id="'.$attributes['id'].'">Pas de serveur HiPay disponible...</div>';
    
    $attributes['method'] = 'post';
    $attributes['url'] = $this->url;
    
    $r = '';
    $r .= '<form ';
    $attributes = $attributes + array('target' => '_top');
    foreach ( $attributes as $key => $value )
      $r .= $key.'="'.$value.'" ';
    $r .= '>';
    
    $r .= '<input type="submit" value="HiPay" />';
    $r .= '</form>';
  }
  
  public static function getTransactionIdByResponse(sfWebRequest $parameters)
  {
    self::config();
    $ipn = new IPN();
    return $ipn->order;
  }
  
  public function response(sfWebRequest $request)
  {
    $bank = $this->createBankPayment($request);
    $bank->save();
    return array('success' => $bank->error != 'yes', 'amount' => $bank->amount);
  }
  
  public function createBankPayment(sfWebRequest $request)
  {
    $bank = new BankPayment;
    $data = $this->process($request->getParameter('xml'));
    
    // the BankPayment Record
    $bank->error = $data['result'] ? 'no' : 'yes';
    foreach ( $data['xml'] as $key => $value )
      $bank->$key = $value;
    
    return $bank;
  }
  
  public function __toString()
  {
    return $this->render();
  }
  
  /** specific methods **/
  
  protected function process($xml)
  {
    $result = HIPAY_MAPI_COMM_XML::analyzeNotificationXML($request->getParameter('xml'), &$mode, &$code, &$date, &$time, &$transid, &$amount, &$currency, &$merchantid, &$merchantdatas, &$customeremail, &$subscriptionid, &$refproducts);
    
    $r = array(
      'result' => $result,
      'xml' => array(
        'capture_mode'         = $mode;
        'code'                 = $code;
        'payment_date'         = $date;
        'payment_$time'        = $time;
        'authorization_id'     = $transid;
        'amount'               = $amount;
        'currency'             = $currency;
        'merchant_id'          = $merchantid;
        'caddie'               = json_encode($merchantdatas);
        'customer_email'       = $customeremail;
        'complementary_code'   = $subscriptionid;
        'transaction_id'       = $refproducts[0];
        'receipt_complement'   = json_encode($refproducts);
        'raw'                  = $request->getParameter('xml');
      ),
    );
    
    return $r;
  }
  public function init()
  {
    return $this
      ->setItem() // taxes + amount
      ->setOrder() // informations
    ;
  }
  public function prepare()
  {
    if ( !$this->order )
      $this->setOrder();
    if ( !$this->item )
      $this->setItem();
    if ( !$this->config )
      $this->config();
    if ( !$this->order || !$this->item || !$this->config )
      throw new liEvenementException('HiPay preconditions failure');
    
    $orderSimple = new HIPAY_MAPI_SimplePayment($payParams, $orderParams, $itemParams);
    $this->output = HIPAY_MAPI_SEND_XML::sendXML($xml = $orderSimple->getXML());
    if ( !HIPAY_MAPI_COMM_XML::analyzeResponseXML($output, &$url, &$err_msg) )
      throw new liOnlineSaleException('An error occurred during HiPay URL generation, with error message: '.$err_msg);
    
    $this->url = $url;
    return $this;
  }
  
  public function setOrder()
  {
    $order = sfConfig::get('app_payment_order',array());
    $this->order = new HIPAY_MAPI_Order();
    $this->order->setOrderTitle($order['title'])
    $this->order->setOrderInfo($this->__('Order #%%tid%%'));
    $this->order->setOrderCategory(isset($order['category_id'] ? $order['category_id'] : 200);
    
    if(!$this->order->check())
      throw new Exception("Error when creating HiPay Order object");
    
    return $this;
  }
  public function setItem()
  {
    $this->item = new HIPAY_MAPI_Product();
    $this->item->setName($this->__('Transaction'));
    $this->item->setquantity(1);
    $this->item->setRef($this->transaction->id);
    
    $price = $this->transaction->getDifferentiatedAmounts();
    $this->item->setPrice($price['value']);
    unset($price['value']);
    $taxes = array();
    foreach ( $price as $t => $val )
    {
      $taxParam = new HIPAY_MAPI_Tax();
      $taxParam->setTaxName($t);
      $taxParam->setTaxVal($val,false);
      $taxes[] = $taxParam;
    }
    $this->item->setTax(array($taxParam));
    
    if ( !$this->item->check() )
      throw new liOnlineSaleException("Error when creating the HiPay Item object");
    
    return $this;
  }
  
  public function config()
  {
    if ( sfConfig::get('app_payment_type','') !== 'hipay' )
      throw new liOnlineSaleException('The configured payment plugin is not set to HiPay, but you are using HiPay...');
    
    // the user
    $this->user = sfContext::hasInstance() ? sfContext::getInstance()->getUser() : NULL;
    
    // we include HiPAY API's package 
    require_once(__DIR__.'/vendor/hipay/mapi_package.php');
    
    // first, we define payement params 
    $this->config = new HIPAY_MAPI_PaymentParams();
    $this->config->setLogin(sfConfig::get('app_payment_id'),sfConfig::get('app_payment_password'));
    // set accounts for order and tax amount
    // you can determine 5 differents accounts; the third concerns insurance, the fourth fixed costs and the last shipping
    $this->config->setAccounts(sfConfig::get('app_payment_account', array()));
    
    $this->config->setLocale($this->user ? $this->user->getCulture() : 'fr_FR');
    $this->config->setMedia('WEB');
    $this->config->setCurrency(sfConfig::get('app_payment_currency'));
    // precise store's order id 
    $this->config->setIdForMerchant(sfConfig::get('app_payment_merchant_id'));
    // payment method : simple or regular
    $this->config->setPaymentMethod(HIPAY_MAPI_METHOD_SIMPLE);
    // when paiement will be really executed (immediately or for some time)
    $this->config->setCaptureDay(HIPAY_MAPI_CAPTURE_IMMEDIATE);
    $this->config->setMerchantSiteId(sfConfig::get('app_payment_site_id'));
    // minimum age of buyer (ALL - everybody is accepted)
    $this->config->setRating('ALL');
    
    // set return URLs 
    $urls = sfConfig::get('app_payment_url', array('normal' => '', 'cancel' => '', 'automatic' => ''));
    $this->config->setUrlOk($urls['done']);
    $this->config->setUrlNok($urls['cancel']);
    $this->config->setUrlCancel($urls['cancel']);
    
    // set notyfication informations
    $this->config->setEmailAck(sfConfig::get('app_informations_email'));
    $this->config->setUrlAck($urls['automatic']);
    
    if( !$this->config->check() )
      throw new liOnlineSaleException("Error when creating HiPay Payment Params object");
    
    return $this;
  }
  
  protected function __($string)
  {
    if ( !sfContext::hasInstance() )
      return $string;
    
    $sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
    return __($string);
  }
}
