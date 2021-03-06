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
$matches = array(
  'declination' => array(
    'model' => 'BoughtProduct',
    'field' => 'product_declination_id',
    'url'   => 'transaction/getStore?id='.$request->getParameter('id').'&state=%s&declination_id='.$params[$field]['declination_id'].'&price_id='.$params[$field]['price_id'],
    'type'  => 'store',
    'data-attr' => 'declination-id',
  ),
  'gauge'       => array(
    'model' => 'Ticket',
    'field' => 'gauge_id',
    'url'   => 'transaction/'.($params[$field]['bunch'] == 'museum' ? 'getPeriods' : 'getManifestations').'?id='.$request->getParameter('id').'&state=%s&gauge_id='.$params[$field]['declination_id'].'&price_id='.$params[$field]['price_id'],
    'type'  => $params[$field]['bunch'] == 'museum' ? 'museum' : 'manifestations',
    'data-attr' => 'gauge-id',
  ),
);

// preparing the DELETE and COUNT queries
switch ( $params[$field]['type'] ) {
case 'gauge':
  $q = Doctrine_Query::create()->from('Ticket a')
    ->andWhere('a.gauge_id = ?',$params[$field]['declination_id'])
    ->andWhere('a.printed_at IS NULL AND a.cancelling IS NULL AND a.duplicating IS NULL')
    ->andWhere('a.transaction_id = ?', $request->getParameter('id'))
    ->orderBy('a.integrated_at IS NULL DESC, a.seat_id IS NULL DESC, a.contact_id IS NULL DESC, a.value ASC, a.integrated_at, a.id DESC')
  ;
  $wips = $q->copy();
  break;
case 'declination':
  $q = Doctrine_Query::create()->from('BoughtProduct a')
    ->andWhere('a.product_declination_id = ?',$params[$field]['declination_id'])
    ->andWhere('a.price_id = ?',$params[$field]['price_id'])
    ->andWhere('a.transaction_id = ?',$request->getParameter('id'))
    ->orderBy('a.integrated_at IS NULL DESC, a.integrated_at, a.value ASC, a.id DESC')
  ;
  break;
}

if ( $params[$field]['price_id'] )
  $q->andWhere('a.price_id = ?',$params[$field]['price_id']);
else
  $q->andWhere('a.price_id IS NULL');

$state = 'false';
if ( isset($params[$field]['state']) && $params[$field]['state'] == 'integrated' )
{
  $state = 'integrated';
  $q->andWhere('a.integrated_at IS NOT NULL');
}
else
  $q->andWhere('a.integrated_at IS NULL');

$this->json['success']['success_fields'][$field]['data'] = array(
  'type'    => $matches[$params[$field]['type']]['type'].'_price',
  'reset'   => true,
  'content' => array(
    'qty'             => $q->count()
                          + $params[$field]['qty'],
    'price_id'        => $params[$field]['price_id'],
    'declination_id'  => $params[$field]['declination_id'],
    'state'           => isset($params[$field]['state']) && $params[$field]['state'] ? $params[$field]['state'] : NULL,
    'transaction_id'  => $request->getParameter('id'),
    'data-attr'       => $matches[$params[$field]['type']]['data-attr'],
  ),
);

// "Pay what you want" feature
$pp = Doctrine::getTable('PriceProduct')->createQuery('pp')
  ->leftJoin('pp.Product p')
  ->leftJoin('p.Declinations d')
  ->andWhere('pp.price_id = ?', $params[$field]['price_id'])
  ->andWhere('d.id = ?',$params[$field]['declination_id'])
  ->select('pp.id, pp.value')
  ->fetchOne()
;
$free_price = $pp && $pp->value === NULL ? $params[$field]['free-price'] : NULL;

$products = NULL;
$last_product = NULL;
$manifs = array();
if ( $params[$field]['qty'] > 0 ) // add
for ( $i = 0 ; $i < $params[$field]['qty'] ; $i++ )
{
  switch ( $params[$field]['type'] ) {
  case 'gauge':
    if ( !$products )
    {
      // tickets to transform
      $wips
        ->andWhere('a.price_id IS NULL')
        ->orderBy('a.seat_id IS NULL DESC, id DESC');
      $products = $wips->execute();
    }
    
    // the current product to create/modify
    $product = $products[$i];
    
    if ( !$product->isNew() )
    {
      $product->price_name = NULL;
      $product->value      = NULL;
      $product->vat        = NULL;
    }
    
    break;
  }
  
  if ( !in_array($params[$field]['type'], array('gauge')) ) // in all cases but gauge and ... create a new object
    $product = new $matches[$params[$field]['type']]['model'];
  
  if ( $free_price )
    $product->value = $free_price;
  
  $product->$matches[$params[$field]['type']]['field'] = $params[$field]['declination_id'];
  $product->price_id = $params[$field]['price_id'];
  $product->transaction_id = $request->getParameter('id');
  
  // optimizing the calculations for big amounts of same products
  if ( $product->isNew() && !is_null($last_product) )
  {
    $go = true;
    foreach ( array('price_id', $matches[$params[$field]['type']]['field'], 'transaction_id') as $f )
    if ( $last_product->$f != $product->$f )
    {
      if ( $request->hasParameter('debug') && sfConfig::get('sf_web_debug',false) )
        error_log('No cache: '.$f.' - '.$last_product->$f.' != '.$product->$f);
      $go = false;
      break;
    }
    if ( $go )
    foreach ( array_merge(
      array('value', 'price_name', 'Price', 'Transaction'),
      in_array($params[$field]['type'], array('gauge')) ? array('Gauge', 'Manifestation') : array()
    ) as $f )
      $product->$f = $last_product->$f;
  }
  
  $product->save();
  $last_product = $product;
}
else // delete
{
  try {
    $q->limit(abs($params[$field]['qty']))
      ->execute()
      ->delete();
  } catch ( liEvenementException $e )
  {
    $this->json['success']['error_fields'][] = __($e->getMessage());
  }
}

$this->json['success']['success_fields'][$field]['remote_content']['load']['type']
  = $this->json['success']['success_fields'][$field]['data']['type'];
$this->json['success']['success_fields'][$field]['remote_content']['load']['url']
  = url_for(sprintf($matches[$params[$field]['type']]['url'], $state), true);
$this->json['success']['success_fields'][$field]['remote_content']['load']['reset'] = false;
