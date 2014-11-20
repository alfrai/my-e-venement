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
    $hipay = new self($transaction);
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
    
    if ( sfConfig::get('app_payment_autosubmit',true) && $this->url )
    {
      header('Location: '.$this->url);
      exit;
    }
    
    foreach ( array('class' => '', 'id' => '') as $attr => $val )
    if ( !isset($attributes[$attr]) )
      $attributes[$attr] = $val;
    
    if ( !$this->url )
      return '<div class="'.$attributes['class'].'" id="'.$attributes['id'].'">Pas de serveur HiPay disponible...</div>';
    
    $attributes['method'] = 'get';
    $attributes['action'] = $this->url;
    
    $r = '';
    $r .= '<form ';
    $attributes = $attributes + array('target' => '_top');
    foreach ( $attributes as $key => $value )
      $r .= $key.'="'.$value.'" ';
    $r .= '>';
    
    $r .= '<input type="submit" value="HiPay" />';
    $r .= '</form>';
    return $r;
  }
  
  public static function getTransactionIdByResponse(sfWebRequest $request)
  {
    $data = self::process($request->getParameter('xml'));
    return $data['xml']['transaction_id'];
  }
  
  public function response(sfWebRequest $request)
  {
    try
    {
      error_log('HiPay pre-response: '.$request->getParameter('xml'));
      $bank = $this->createBankPayment($request);
      error_log('HiPay post-response: '.($bank->error == 'yes' ? 'global failure' : 'global success for #'.$bank->transaction_id));
      $bank->save();
    }
    catch ( Exception $e )
    {
      error_log($e);
    }
    return array('success' => $bank->error === 'no', 'amount' => $bank->amount);
  }
  
  public function createBankPayment(sfWebRequest $request)
  {
    $bank = new BankPayment;
    $data = $this->process($request->getParameter('xml'));
    
    // the BankPayment Record
    if ( !$data['result'] )
      $bank->error = 'yes';
    foreach ( $data['xml'] as $key => $value )
      $bank->$key = $value;
    $bank->error = $bank->code == 'ok' ? 'no' : 'yes';
    
    return $bank;
  }
  
  public function __toString()
  {
    try { return $this->render(); }
    catch ( Exception $e ) { error_log($e->getMessage()); return ''; }
  }
  
  /** specific methods **/
  
  protected static function process($xml)
  {
    $result = HIPAY_MAPI_COMM_XML::analyzeNotificationXML($xml, $mode, $code, $date, $time, $transid, $amount, $currency, $merchantid, $merchantdatas, $customeremail, $subscriptionid, $refproducts);
    
    $r = array(
      'result' => $result,
      'xml' => array(
        'capture_mode'         => $mode,
        'code'                 => $code,
        'payment_date'         => $date,
        'payment_time'         => $time,
        'authorization_id'     => $transid,
        'amount'               => $amount,
        'currency_code'        => $currency,
        'merchant_id'          => $merchantid,
        'caddie'               => json_encode($merchantdatas),
        'customer_email'       => $customeremail,
        'complementary_code'   => $subscriptionid,
        'transaction_id'       => $refproducts[0],
        'receipt_complement'   => json_encode($refproducts),
        'raw'                  => $xml,
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
    $url = sfConfig::get('app_payment_url', array());
    
    $orderSimple = new HIPAY_MAPI_SimplePayment($this->config, $this->order, array($this->item));
    $output = HIPAY_MAPI_SEND_XML::sendXML($xml = $orderSimple->getXML(), isset($url['hipay_order']) ? $url['hipay_order'] : NULL);
    if ( !HIPAY_MAPI_COMM_XML::analyzeResponseXML($output, $url, $err_msg) )
    {
      throw new liOnlineSaleException('An error occurred during HiPay URL generation, with error message: '.$err_msg);
    }
    
    $this->url = $url;
    error_log('HiPay prepare: #'.$this->transaction->id.' / '.$this->url);
    return $this;
  }
  
  public function setOrder()
  {
    $order = sfConfig::get('app_payment_order',array());
    $this->order = new HIPAY_MAPI_Order();
    $this->order->setOrderInfo($order['title'] ? $order['title'] : $this->__('Order #%%tid%%', array('%%tid%%' => $this->transaction->id)));
    $this->order->setOrderTitle($this->__('Order #%%tid%%', array('%%tid%%' => $this->transaction->id)));
    $this->order->setOrderCategory(isset($order['category_id']) ? $order['category_id'] : 618);
    
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
    $this->item->setCategory(sfConfig::get('app_payment_category',200));
    $this->item->setPrice($this->transaction->getPrice(true, true));
    
    if ( !$this->item->check() )
      throw new liOnlineSaleException("Error when creating the HiPay Item object");
    
    return $this;
  }
  
  public function config()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
    if ( sfConfig::get('app_payment_type','') !== 'hipay' )
      throw new liOnlineSaleException('The configured payment plugin is not set to HiPay, but you are using HiPay...');
    
    // the user
    $this->user = sfContext::hasInstance() ? sfContext::getInstance()->getUser() : NULL;
    
    // we include HiPAY API's package 
    require_once(__DIR__.'/vendor/hipay/mapi_package.php');
    
    // first, we define payement params 
    $this->config = new HiPayPaymentParams();
    $this->config->setLogin(sfConfig::get('app_payment_id'), sfConfig::get('app_payment_password'));
    $this->config->setIssuerAccountLogin($this->transaction->Contact->email);
    // set accounts for order and tax amount
    // you can determine 5 differents accounts; the third concerns insurance, the fourth fixed costs and the last shipping
    $this->config->setAccountsBulk(sfConfig::get('app_payment_account', array()));
    
    $this->config->setLocale($this->user ? $this->user->getCulture() : 'fr_FR');
    $this->config->setMedia(sfConfig::get('app_payment_media', 'WEB'));
    $this->config->setCurrency(sfConfig::get('app_payment_currency', 'EUR'));
    // payment method : simple or regular
    $this->config->setPaymentMethod(HIPAY_MAPI_METHOD_SIMPLE);
    // when paiement will be really executed (immediately or for some time)
    $this->config->setCaptureDay(HIPAY_MAPI_CAPTURE_IMMEDIATE);
    $this->config->setMerchantSiteId(sfConfig::get('app_payment_site_id'));
    // minimum age of buyer (ALL - everybody is accepted)
    $this->config->setRating('ALL');
    
    // precise store's order id 
    $this->config->setIdForMerchant($this->transaction->id);
    
    // set return URLs 
    $urls = sfConfig::get('app_payment_url', array('normal' => '', 'cancel' => '', 'automatic' => ''));
    $this->config->setUrlOk(url_for($urls['normal'],true));
    $this->config->setUrlNok(url_for($urls['cancel'],true));
    $this->config->setUrlCancel(url_for($urls['cancel'],true));
    
    // set notyfication informations
    if ( sfConfig::get('app_informations_email', false) )
    $this->config->setEmailAck(sfConfig::get('app_informations_email'));
    $this->config->setUrlAck(url_for($urls['automatic'],true));
    
    if ( !$this->config->check() )
      throw new liOnlineSaleException("Error when creating HiPay Payment Params object");
    
    return $this;
  }
  
  protected function __($string, $params = array())
  {
    if ( !sfContext::hasInstance() )
      return $string;
    
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
    return __($string, $params);
  }
}
