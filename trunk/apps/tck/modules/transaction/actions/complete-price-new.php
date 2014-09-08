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
    'model' => 'BoughtProduct',
    'field' => 'gauge_id',
    'url'   => 'transaction/getManifestations?id='.$request->getParameter('id').'&state=%s&gauge_id='.$params[$field]['declination_id'].'&price_id='.$params[$field]['price_id'],
    'type'  => 'manifestations',
    'data-attr' => 'gauge-id',
  ),
);

// preparing the DELETE and COUNT queries
switch ( $params[$field]['type'] ) {
case 'gauge':
  $q = Doctrine_Query::create()->from('Ticket a')
    ->andWhere('a.gauge_id = ?',$params[$field]['declination_id'])
    ->andWhere('a.price_id = ?',$params[$field]['price_id'])
    ->andWhere('a.printed_at IS NULL AND a.cancelling IS NULL AND a.duplicating IS NULL')
    ->orderBy('a.integrated_at IS NULL DESC, a.integrated_at, a.seat_id IS NULL DESC, a.value ASC, a.id DESC')
  ;
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
    'qty'             => $q->copy()->andWhere('a.price_id = ?',$params[$field]['price_id'])->count()
                          + $params[$field]['qty'],
    'price_id'        => $params[$field]['price_id'],
    'declination_id'  => $params[$field]['declination_id'],
    'state'           => isset($params[$field]['state']) && $params[$field]['state'] ? $params[$field]['state'] : NULL,
    'transaction_id'  => $request->getParameter('id'),
    'data-attr'        => $matches[$params[$field]['type']]['data-attr'],
  ),
);

// Pay what you want feature
$pp = Doctrine::getTable('PriceProduct')->createQuery('pp')
  ->leftJoin('pp.Product p')
  ->leftJoin('p.Declinations d')
  ->andWhere('pp.price_id = ?', $params[$field]['price_id'])
  ->andWhere('d.id = ?',$params[$field]['declination_id'])
  ->select('pp.id, pp.value')
;
$free_price = $pp->fetchOne()->value === NULL ? $params[$field]['free-price'] : NULL;


$products = NULL;
$manifs = array();
if ( $params[$field]['qty'] > 0 ) // add
for ( $i = 0 ; $i < $params[$field]['qty'] ; $i++ )
{
  switch ( $params[$field]['type'] ) {
  case 'gauge':
    if ( !$products )
    {
      // tickets to transform
      $q
        ->andWhere('a.price_id IS NULL')
        ->orderBy('a.seat_id IS NULL DESC, id DESC');
      $products = $q->execute();
    }
    
    // the current product to create/modify
    $product = $products[$i];
    
    if ( !$product->isNew() )
    {
      $product->price_name = NULL;
      $product->value      = NULL;
      $product->vat        = NULL;
    }
    // no break, continue
  default:
    if ( !in_array($params[$field]['type'], array('gauge')) ) // in all cases but gauge and ... create a new object
      $product = new $matches[$params[$field]['type']]['model'];
    
    $product->$matches[$params[$field]['type']]['field'] = $params[$field]['declination_id'];
    $product->price_id = $params[$field]['price_id'];
    $product->transaction_id = $request->getParameter('id');
    if ( $free_price )
      $product->value = $free_price;
    $product->save();
  }
}
else // delete
{
  $q->andWhere('a.price_id = ?',$params[$field]['price_id'])
    ->limit(abs($params[$field]['qty']))
    ->execute()
    ->delete();
}

$this->json['success']['success_fields'][$field]['remote_content']['load']['type']
  = $this->json['success']['success_fields'][$field]['data']['type'];
$this->json['success']['success_fields'][$field]['remote_content']['load']['url']
  = url_for(sprintf($matches[$params[$field]['type']]['url'], $state), true);
