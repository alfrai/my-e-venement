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
*
***********************************************************************************/
?>
<?php
  
  class PayboxPayment extends OnlinePayment
  {
    protected $name = 'paybox';
    protected $url = array();
    protected $site, $rang, $id, $hash;
    
    public static function create(Transaction $transaction)
    {
      return new self($transaction);
    }
    
    public static function response($get)
    {
      // renewing the paybox's key cache
      $pem = sfConfig::get('app_payment_pem',array());
      if ( !isset($pem['local'] ) ) $pem['local']  = 'paybox.pem';
      if ( !isset($pem['remote']) ) $pem['remote'] = 'http://www1.paybox.com/telechargements/pubkey.pem';
      $fp = fopen($path = sfConfig::get('sf_upload_dir').'/'.$pem['local'],'a+');
      $stat = fstat($fp);
      if ( $stat['size'] == 0 || $stat['mtime'] < strtotime('yesterday') )
        fwrite($fp, file_get_contents($pem['remote']));
      fclose($fp);
      
      // getting the paybox's public key
      $cert = file_get_contents(sfConfig::get('sf_upload_dir').'/'.$pem['local']);
      $pubkeyid = openssl_get_publickey($cert);
      
      $signature = base64_decode($get['signature']);
      unset($get['signature']);
      $str = array();
      foreach ( $get as $key => $val )
        $str[] = $key.'='.$val;
      $str = implode('&',$str);
      
      switch ( openssl_verify($str, $signature, $pubkeyid) ) {
      case 1:
        break;
      case 0:
        throw new liOnlineSaleException(sprintf('Bad signature recieved from Paybox. signature: %s / signed-string: %s / GET: %s',$signature,$str,print_r($get,true)));
      default:
        throw new liOnlineSaleException(sprintf('Impossible to parse this signature : %s',$signature));
      }
      
      return array('success' => $get['error'] === '00000', 'amount' => $get['amount']);
    }
    
    protected function __construct(Transaction $transaction)
    {
      // the configuration
      $this->id       = sfConfig::get('app_payment_id');
      $this->rank     = sfConfig::get('app_payment_rank');
      $this->site     = sfConfig::get('app_payment_site');
      $this->currency = sfConfig::get('app_payment_currency','978');
      $this->return   = sfConfig::get('app_payment_return','amount:M;transaction_id:R;card_type:C;ip_country:I;paybox_id:S;authorisation:A;error:E;signature:K');
      $this->hash     = sfConfig::get('app_payment_hash','SHA512');
      $this->key      = sfConfig::get('app_payment_key');
      $this->url      = sfConfig::get('app_payment_url',array());
      $this->autosubmit = sfConfig::get('app_payment_autosubmit',true);
      $this->datetime = date('c');
      
      // the transaction and the amount
      $this->transaction = $transaction;
      $this->value = $this->transaction->getPrice(true)
        + $this->transaction->getMemberCardPrice(true)
        - $this->transaction->getTicketsLinkedToMemberCardPrice(true);
    }
    
    public function render($attributes)
    {
      $url = $this->getTPEWebURL();
      if ( !$url )
        return '<div class="'.$attributes['class'].'" id="'.$attributes['id'].'">Pas de serveur Paybox disponible...</div>';
      
      $r = '';
      $r .= '<form action="'.$url.'" method="post" ';
      foreach ( $attributes as $key => $value )
        $r .= $key.'="'.$value.'" ';
      $r .= '>';
      
      foreach ( $this->getPayboxVars() as $name => $value )
        $r .= "\n".'<input type="hidden" name="'.$name.'" value="'.$value.'" />';
      
      $r .= '<input type="submit" value="Paybox" />';
      $r .= '</form>';
      
      return $r;
    }

    public function __toString()
    {
      try {
        return $this->render(array('class' => 'autosubmit', 'id' => 'payment-form'));
      }
      catch ( sfException $e )
      {
        return 'An error occurred creating the link with the bank';
      }
    }
    
    public function getHmac()
    {
      $arr = array();
      foreach ( $this->getPayboxVarsWithoutHmac() as $key => $val )
        $arr[] = $key.'='.$val;
      $str = implode('&',$arr);
      
      return strtoupper(hash_hmac($this->hash, $str, pack('H*',$this->key)));
    }
    public function getPayboxVars()
    {
      $arr = $this->getPayboxVarsWithoutHmac();
      $arr['PBX_HMAC'] = $this->getHmac();
      return $arr;
    }
    
    public function getPayboxVarsWithoutHmac()
    {
      sfContext::getInstance()->getConfiguration()->loadHelpers('Url');
      
      $arr = array();
      $arr['PBX_SITE'] = $this->site;
      $arr['PBX_RANG'] = $this->rank;
      $arr['PBX_IDENTIFIANT' ] = $this->id;
      $arr['PBX_TOTAL'] = $this->value*100;
      $arr['PBX_DEVISE'] = $this->currency;
      $arr['PBX_CMD'] = $this->transaction->id;
      $arr['PBX_PORTEUR'] = $this->transaction->Contact->email;
      $arr['PBX_RETOUR'] = $this->return;
      $arr['PBX_HASH'] = $this->hash;
      $arr['PBX_TIME'] = $this->datetime;
      $arr['PBX_EFFECTUE'] = url_for($this->url['normal'],true);
      $arr['PBX_ANNULE']   = url_for($this->url['cancel'],true);
      $arr['PBX_REFUSE']   = url_for($this->url['cancel'],true);
      $arr['PBX_REPONDRE_A'] = url_for($this->url['automatic'],true);
      
      return $arr;
    }
    
    // get a functionnal web server for bank requests
    public function getTPEWebURL()
    {
      $r = false;
      
      foreach ( $this->url['payment'] as $url )
      {
        if ( count($this->url['payment']) == 1 )
          return $this->url['payment'][0].$this->url['uri'];
        
        $doc = new DOMDocument();
        $doc->loadHTMLFile($url.'load.html');
        
        $status = '';
        $element = $doc->getElementById('server_status');
        if ( $element )
          $status = $element->textContent;
        
        if ( $status == 'OK' )
        {
          $r = $url.$this->url['uri'];
          break;
        }
      }
      
      return $r;
    }
  }
