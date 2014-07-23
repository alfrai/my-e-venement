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
  
  class TipiPayment extends OnlinePayment
  {
    protected $name = 'tipi';
    protected $url = array();
    protected $site, $rang, $id, $hash;
    
    public static function create(Transaction $transaction)
    {
      return new self($transaction);
    }
    
    public static function response($all)
    {
      // origin of the request
      $url = sfConfig::get('app_payment_url',array());
      $buf = preg_replace(
        array('!^http\w{0,1}://!', '!/$!'),
        array('', ''),
        $url['payment']
      );
      $addresses = gethostbynamel($buf[0]);
      if ( !in_array($all['ip_address'], $addresses) )
        throw new liOnlineSaleException('TIPI ERROR: The request has a bad origin.');
      
      // tokens
      if ( $all['token'] != $all['given_token'] )
        throw new liOnlineSaleException('TIPI ERROR: The given token is incorrect');
      
      // the result given by TIPI
      if ( $all['result'] !== 'P' )
        throw new liOnlineSaleException('TIPI ERROR: The payment has been refused or cancelled');
      
      if ( Doctrine::getTable('Payment')->createQuery('p')
        ->andWhere('p.transaction_id = ?',$all['transaction_id'])
        ->andWhere('p.payment_method_id = ?', sfConfig::get('app_tickets_payment_method_id',''))
        ->count() > 0 )
        throw new liOnlineSaleException('TIPI ERROR: The payment has already been recorded (common TIPI mistake based on a strange TIPI behaviour)');
      
      return true;
    }
    
    public static function getToken($id = '', $amount = 0)
    {
      return md5($id.'-'.$amount.'-'.sfConfig::get('app_payment_salt','26bc277b00189a32f8349cbf0a361519'));
    }
    
    protected function __construct(Transaction $transaction)
    {
      // the configuration
      $this->id       = str_pad(sfConfig::get('app_payment_id', 194), 6, '0', STR_PAD_LEFT);
      $this->refdet   = str_pad(sfConfig::get('app_payment_refdet', 999900000000999999),18,'0',STR_PAD_LEFT);
      $this->email    = $transaction->Contact->email;
      $url = sfConfig::get('app_payment_url', array('response' => 'cart/response'));
      $this->subject  = 'Transaction n'.$transaction->id;
      $this->mode     = sfConfig::get('app_payment_prod', false) ? 'X' : 'T';
      $this->autosubmit = sfConfig::get('app_payment_autosubmit',true);
      
      // the transaction and the amount
      $this->transaction = $transaction;
      $this->value = $this->transaction->getPrice(true)
        + $this->transaction->getMemberCardPrice(true)
        - $this->transaction->getTicketsLinkedToMemberCardPrice(true);
      
      $this->url      = url_for($url['response']
        .'?transaction_id='.$transaction->id
        .'&token='.self::getToken($transaction->id, $this->value)
      ,true);
    }
    
    public function render($attributes)
    {
      $url = $this->getTPEWebURL();
      if ( !$url )
        return '<div class="'.$attributes['class'].'" id="'.$attributes['id'].'">Pas de serveur Paybox disponible...</div>';
      
      $r = '';
      $r .= '<form action="'.$url.'" method="get" ';
      foreach ( $attributes as $key => $value )
        $r .= $key.'="'.$value.'" ';
      $r .= '>';
      
      foreach ( $this->getTipiVars() as $name => $value )
        $r .= "\n".'<input type="hidden" name="'.$name.'" value="'.$value.'" />';
      
      $r .= '<input type="submit" value="Tipi" />';
      $r .= '</form>';
      
      return $r;
    }

    public function __toString()
    {
      try {
        return $this->render(array(
          'class' => sfConfig::get('app_payment_autosubmit',true) ? 'autosubmit' : '',
          'id' => 'payment-form'
        ));
      }
      catch ( sfException $e )
      {
        return 'An error occurred creating the link with the bank';
      }
    }
    
    public function getTipiVars()
    {
      sfContext::getInstance()->getConfiguration()->loadHelpers('Url');
      
      $arr = array();
      $arr['numcli']  = $this->id;
      $arr['refdet']  = $this->refdet;
      $arr['montant'] = $this->value*100;
      $arr['mel']     = $this->email;
      $arr['saisie']  = $this->mode;
      $arr['urlcl']   = $this->url;
      $arr['objet']   = $this->subject;
      
      return $arr;
    }
    
    // get a functionnal web server for bank requests
    public function getTPEWebURL()
    {
      $urls = sfConfig::get('app_payment_url');
      return $urls['payment'][0].$urls['uri'];
    }
  }
