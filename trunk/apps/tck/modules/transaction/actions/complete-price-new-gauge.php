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
$q = Doctrine_Query::create()->from('Ticket tck')
  ->andWhere('tck.gauge_id = ?',$params[$field]['declination_id'])
  ->andWhere('tck.transaction_id = ?',$request->getParameter('id'))
  ->andWhere('tck.printed_at IS NULL')
  ->orderBy('tck.integrated_at IS NULL DESC, tck.integrated_at, tck.seat_id IS NULL DESC, id DESC');

$state = 'false';
if ( isset($params[$field]['state']) && $params[$field]['state'] == 'integrated' )
{
  $state = 'integrated';
  $q->andWhere('tck.integrated_at IS NOT NULL');
}
else
  $q->andWhere('tck.integrated_at IS NULL');
        
$this->json['success']['success_fields'][$field]['data'] = array(
  'type'    => 'gauge_price',
  'reset'   => true,
  'content' => array(
    'qty'             => $q->copy()->andWhere('tck.price_id = ?',$params[$field]['price_id'])->count()
                          + $params[$field]['qty'],
    'price_id'        => $params[$field]['price_id'],
    'declination_id'  => $params[$field]['declination_id'],
    'state'           => isset($params[$field]['state']) && $params[$field]['state'] ? $params[$field]['state'] : NULL,
    'transaction_id'  => $request->getParameter('id'),
  ),
);

$manifs = array();
if ( $params[$field]['qty'] > 0 ) // add
{
  // tickets to transform
  $q
    ->andWhere('tck.price_id IS NULL')
    ->orderBy('tck.seat_id IS NULL DESC, id DESC');
  $tickets = $q->execute();
  
  for ( $i = 0 ; $i < $params[$field]['qty'] ; $i++ )
  {
    $ticket = $tickets[$i];
    if ( !$ticket->isNew() )
    {
      $ticket->price_name = NULL;
      $ticket->value      = NULL;
      $ticket->vat        = NULL;
    }
    
    $ticket->gauge_id = $params[$field]['declination_id'];
    $ticket->price_id = $params[$field]['price_id'];
    $ticket->transaction_id = $request->getParameter('id');
    $ticket->save();
  }
}
else // delete
{
  $q->andWhere('tck.price_id = ?',$params[$field]['price_id'])
    ->limit(abs($params[$field]['qty']))
    ->execute()
    ->delete();
}

$this->json['success']['success_fields'][$field]['remote_content']['load']['type']
  = 'gauge_price';
$this->json['success']['success_fields'][$field]['remote_content']['load']['url']
  = url_for('transaction/getManifestations?id='.$request->getParameter('id').'&state='.$state.'&gauge_id='.$params[$field]['declination_id'].'&price_id='.$params[$field]['price_id'], true);
