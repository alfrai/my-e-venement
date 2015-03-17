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
  ->andWhere('bp.product_declination_id = ?', $store['declination_id'])
  ->andWhere('bp.price_id = ?', $store['price_id'])
  ->orderBy('bp.value, bp.id DESC')
;
$count = $q->copy()->andWhere('bp.integrated_at IS NULL')->count();
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

// "pay what you want" feature
$pp = Doctrine::getTable('PriceProduct')->createQuery('pp')
  ->leftJoin('pp.Product p')
  ->leftJoin('p.Declinations d')
  ->andWhere('pp.price_id = ?', $store['price_id'])
  ->andWhere('d.id = ?',$store['declination_id'])
  ->select('pp.id, pp.value')
;
$free_price = is_null($pp->fetchOne()->value)
  ? floatval(
    floatval($store['free-price']) <= 0
    ? sfConfig::get('project_tickets_free_price_default', 1)
    : $store['free-price']
  )
  : NULL;
$qty = !is_null($free_price) ? $store['qty'] * 2 - 1 : $store['qty'] - $count;

if ( $qty == 0 )
{
  $this->json['success']['qty'] = $q->andWhere('bp.integrated_at IS NULL OR bp.member_card_id IS NOT NULL')->count();
  $this->json['success']['message'] = 'Nothing to declare...';
}
elseif ( $qty < 0 )
{
  $q->limit(abs($qty));
  if ( !is_null($free_price) )
    $q->andWhere('bp.value = ?', $free_price);
  $bps = $q->execute();
  $nb = $bps->count();
  $bps->delete();
  $this->json['success']['qty'] = $nb;
}
elseif ( $qty > 0 )
{
  // adding ...
  for ( $i = 0 ; $i < ($free_price ? 1 : $qty) ; $i++ )
  {
    $bp = new BoughtProduct;
    $bp->product_declination_id = $store['declination_id'];
    $bp->price_id = $store['price_id'];
    $bp->transaction_id = $this->getUser()->getTransactionId();
    if ( !is_null($free_price) )
      $bp->value = $free_price;
    $bp->save();
    $this->json['success']['qty'] = $q->count();
  }
}

if (!( $request->hasParameter('debug') && sfConfig::get('sf_web_debug', false) ))
  sfConfig::set('sf_web_debug', false);
else
{
  $this->setLayout(sfConfig::get('app_options_template', 'public'));
  $this->getResponse()->setContentType('text/html');
}
return 'Success';
