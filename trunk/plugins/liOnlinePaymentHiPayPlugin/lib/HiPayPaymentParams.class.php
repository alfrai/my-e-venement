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

class HiPayPaymentParams extends HIPAY_MAPI_PaymentParams
{
  public function getXmlElement()
  {
    return get_parent_class($this);
  }
  public function setAccountsBulk($accounts)
  {
    if ( !is_array($accounts) )
      $accounts = array('item' => $accounts);
    foreach ( array('tax', 'insurance', 'fixed', 'shipping') as $key )
    if ( !isset($accounts[$key]) )
      $accounts[$key] = 0;
    
    return $this->setAccounts(
      $accounts['item'],
      $accounts['tax'],
      $accounts['insurance'],
      $accounts['fixed'],
      $accounts['shipping']
    );
  }
}
