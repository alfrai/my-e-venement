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
// preparing the DELETE and COUNT queries
$q = Doctrine_Query::create()->from('BoughtProduct bp')
  ->andWhere('bp.product_declination_id = ?',$params[$field]['declination_id'])
  ->andWhere('bp.price_id = ?',$params[$field]['price_id'])
  ->andWhere('bp.transaction_id = ?',$request->getParameter('id'))
  ->orderBy('bp.integrated_at IS NULL DESC, bp.integrated_at, id DESC');

$state = 'false';
if ( isset($params[$field]['state']) && $params[$field]['state'] == 'integrated' )
{
  $state = 'integrated';
  $q->andWhere('bp.integrated_at IS NOT NULL');
}
else
  $q->andWhere('bp.integrated_at IS NULL');
        
$this->json['success']['success_fields'][$field]['data'] = array(
  'type'  => 'gauge_price',
  'reset' => true,
  'content' => array(
    'qty'             => $q->count() + $params[$field]['qty'],
    'price_id'        => $params[$field]['price_id'],
    'declination_id'  => $params[$field]['declination_id'],
    'state'           => isset($params[$field]['state']) && $params[$field]['state'] ? $params[$field]['state'] : NULL,
    'transaction_id'  => $request->getParameter('id'),
  ),
);

if ( $params[$field]['qty'] > 0 ) // add
{
  for ( $i = 0 ; $i < $params[$field]['qty'] ; $i++ )
  {
    $bp = new BoughtProduct;
    $bp->product_declination_id = $params[$field]['declination_id'];
    $bp->price_id = $params[$field]['price_id'];
    $bp->transaction_id = $request->getParameter('id');
    $bp->save();
  }
}
else // delete
{
  $q->limit(abs($params[$field]['qty']))
    ->execute()
    ->delete();
}

$this->json['success']['success_fields'][$field]['remote_content']['load']['type']
  = 'declination_price';
$this->json['success']['success_fields'][$field]['remote_content']['load']['url']
  = url_for('transaction/getStore?id='.$request->getParameter('id').'&state='.$state.'&declination_id='.$params[$field]['declination_id'].'&price_id='.$params[$field]['price_id'], true);
