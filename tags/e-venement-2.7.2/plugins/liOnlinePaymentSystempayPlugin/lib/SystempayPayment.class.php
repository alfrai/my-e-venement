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

class SystempayPayment extends OnlinePayment
{
  const name = 'systempay';
  protected $value = 0;
  protected $params = array();
  
  public static function create(Transaction $transaction)
  {
    $p = new self($transaction);
    $p->configure();
    return $p;
  }
  
  protected function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('Url');
    
    // mandatory
    $this->params = array(
      'vads_site_id'          => sfConfig::get('app_payment_id'),
      'vads_trans_id'         => str_pad(Doctrine::getTable('Transaction')->createQuery('t')->andWhere('t.created_at >= ?', date('Y-m-d'))->count()+1, 6, '0', STR_PAD_LEFT),
      'vads_currency'         => sfConfig::get('app_payment_currency', 978),
      'vads_ctx_mode'         => strtoupper(sfConfig::get('app_payment_mode', 'production')),
      'vads_amount'           => $this->value*100,
      'vads_trans_date'       => gmdate('YmdHis'),
      'vads_version'          => 'V2',
      'vads_payment_config'   => 'SINGLE',
      'vads_page_action'      => 'PAYMENT',
      'vads_action_mode'      => 'INTERACTIVE',
    );
    
    // optionals
    $validation = array('auto' => 0, 'manual' => 1);
    $urls = sfConfig::get('app_payment_url', array());
    $this->params = $this->params + array(
      'vads_order_id'         => $this->transaction->id,
      'vads_cust_email'       => $this->transaction->Contact->email,
      'vads_cust_id'          => $this->transaction->contact_id,
      'vads_contrib'          => sfConfig::get('software_about_name').' '.sfConfig::get('software_about_version'),
      'vads_validation_mode'  => $validation[sfConfig::get('app_payment_validation', 'auto')],
      'vads_success_timeout'  => 0,
      'vads_url_error'        => url_for($urls['cancel'],true),
      'vads_url_referral'     => url_for($urls['cancel'],true),
      'vads_url_return'       => url_for($urls['cancel'],true),
      'vads_url_refused'      => url_for($urls['cancel'],true),
      'vads_url_cancel'       => url_for($urls['cancel'],true),
      'vads_url_success'      => url_for($urls['normal'],true),
      'vads_shop_url'         => url_for('/',true),
      'vads_return_mode'      => 'GET',
      'vads_language'         => in_array(sfContext::getInstance()->getUser()->getCulture(), array('fr','de','en','zh','es','it','ja','pt','nl','sv')) ? sfContext::getInstance()->getUser()->getCulture() : sfConfig::get('app_payment_default_language','fr'),
      'vads_available_languages' => implode(',', array_keys(sfConfig::get('project_internals_cultures', array('fr' => 'Français')))),
    );
    
    // more than optional
    if ( sfConfig::get('app_payment_delay', false) )
      $this->params['vads_capture_delay'] = sfConfig::get('app_payment_delay');
    
    $this->sign();
  }
  
  protected function sign($params = array())
  {
    $use_this = false;
    if ( count($params) == 0 )
    {
      $params = $this->params;
      $use_this = true;
    }
    
    unset($params['signature']);
    
    $arr = array();
    foreach ( $params as $key => $value )
    if ( substr($key, 0, 5) == 'vads_' )
      $arr[$key] = $value;
    ksort($arr);
    $arr[] = sfConfig::get('app_payment_certificate');
    $base = implode('+',$arr);
    
    $params['signature'] = sha1($base);
    
    if ( $use_this )
      $this->params = $params;
    return $params;
  }
  
  public function verifySignature(array $params)
  {
    $new = $this->sign($params);
    if ( $new['signature'] === $params['signature'] )
      return true;
  }
  
  public function __toString()
  {
    try {
     return $this->render();
    }
    catch ( liOnlineSaleException $e )
    {
      return $e->getMessage();
    }
  }
  
  // generates the request
  public function render(array $attributes = array())
  {
    $urls = sfConfig::get('app_payment_url', array());
    if (!( isset($urls['bank']) && $urls['bank'] ))
      throw new liOnlineSaleException('No URL found for the Systempay payment.');
    
    if ( sfConfig::get('app_payment_auto_follow', true) )
    {
      if ( !isset($attributes['class']) )
        $attributes['class'] = '';
      $attributes['class'] .= ' autosubmit';
    }
    
    $r = '';
    $r .= '<form action="'.$urls['bank'].'" method="post" ';
    $attributes = $attributes + array('target' => '_top');
    foreach ( $attributes as $key => $value )
      $r .= $key.'="'.$value.'" ';
    $r .= '>';
    
    foreach ( $this->params as $name => $value )
      $r .= "\n".'<input type="hidden" name="'.$name.'" value="'.$value.'" />';
    
    $r .= '<input type="submit" value="systempay" />';
    $r .= '</form>';
    
    return $r;
  }
  
  public static function getTransactionIdByResponse(sfWebRequest $request)
  {
    if (!( $request->hasParameter('vads_order_id') && intval($request->getParameter('vads_order_id')).'' === ''.$request->getParameter('vads_order_id') ))
      throw new liOnlineSaleException('No transaction id returned... Impossible to make the link with the original transaction. Validation abandonned.');
    return intval($request->getParameter('vads_order_id'));
  }
  public function response(sfWebRequest $request)
  {
    $bank = $this->createBankPayment($request);
    $bank->save();
    
    $params = $request->getPostParameters();
    if ( !$this->verifySignature($params) )
      throw new liOnlineSaleException('The given signature is wrong ! Check out transaction #'.$transaction->id.'.');
    
    if ( $bank->code === '00' )
      return array('success' => true, 'amount' => $bank->amount);
    else
      return array('success' => false);
  }
  
  public function createBankPayment(sfWebRequest $request)
  {
    $bank = new BankPayment;
    
    // the BankPayment Record
    $bank->code = $request->getParameter('vads_auth_result','');
    $bank->payment_certificate = $request->getParameter('signature').' '.($this->verifySignature($request->getPostParameters()) ? 'signature passed' : 'signature failed');
    $bank->authorization_id = $request->getParameter('vads_trans_id').' / '.$request->getParameter('vads_auth_number','');
    $bank->transaction_id = $request->getParameter('vads_order_id');
    $bank->merchant_id = sfConfig::get('app_payment_id', '111222333444');
    $bank->capture_mode = self::name;
    $bank->amount = $request->getParameter('vads_effective_amount')/100;
    $bank->raw = json_encode($request->getPostParameters());
    
    return $bank;
  }
  
}
