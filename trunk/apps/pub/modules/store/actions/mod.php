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

$store = $request->getParameter('store');
$this->json = array(
  'error' => array(),
  'success' => array(
    'price_id' => $store['price_id'],
    'declination_id' => $store['declination_id'],
    'qty' => 0,
  ),
);

$q = Doctrine::getTable('BoughtProduct')->createQuery('bp')
  ->andWhere('bp.transaction_id = ?', $this->getUser()->getTransactionId())
  ->andWhere('bp.integrated_at IS NULL')
  ->andWhere('bp.product_declination_id = ?', $store['declination_id'])
  ->andWhere('bp.price_id = ?', $store['price_id'])
  ->orderBy('bp.value, bp.id DESC')
;
$count = $q->count();
if ( $count == 0 )
{
  // security checks
  $check = Doctrine::getTable('ProductDeclination')->createQuery('d')
    ->andWhere('d.id = ?', $store['declination_id'])
    ->leftJoin('d.Product p')
    ->leftJoin('p.Prices price')
    ->andWhere('price.id = ?', $store['price_id'])
    ->leftJoin('price.Users pu')
    ->andWhere('pu.id = ?', $this->getUser()->getId())
  ;
  if ( $check->count() == 0 )
    return 'Error';
}

$qty = $store['qty'] - $count;
if ( $qty == 0 )
  $this->json['success']['qty'] = $q->count();
elseif ( $qty < 0 )
{
  $bps = $q->limit(abs($qty))
    ->execute();
  $bps->delete();
  $this->json['success']['qty'] = $q->count();
}
elseif ( $qty > 0 )
for ( $i = 0 ; $i < $qty ; $i++ )
{
  $bp = new BoughtProduct;
  $bp->product_declination_id = $store['declination_id'];
  $bp->price_id = $store['price_id'];
  $bp->transaction_id = $this->getUser()->getTransactionId();
  $bp->save();
  $this->json['success']['qty'] = $q->count();
}

if (!( $request->hasParameter('debug') && sfConfig::get('sf_web_debug', false) ))
  sfConfig::set('sf_web_debug', false);
else
{
  $this->setLayout('public');
  $this->getResponse()->setContentType('text/html');
}
return 'Success';
