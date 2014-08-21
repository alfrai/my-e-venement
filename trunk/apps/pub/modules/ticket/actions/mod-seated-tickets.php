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
  $this->json = array('error' => false, 'success' => false);
  $this->debug($request);
  
  $cpt = 0;
  foreach ( $request->getParameter('seats', array()) as $seat )
  {
    $wip = Doctrine::getTable('Ticket')->createQuery('tck')
      ->select('tck.*')
      ->leftJoin('tck.Seat s')
      ->leftJoin('tck.Gauge g')
      ->andWhere('tck.transaction_id = ?', $this->getUser()->getTransaction()->id)
      ->andWhere('s.id = ?', $seat['seat_id'])
      ->andWhere('g.id = ?', $seat['gauge_id'])
      ->fetchOne()
    ;
    
    if ( !$wip )
      return $this->jsonError('The given seat is not available for this gauge, try again.');
    
    // affecting a price
    if ( isset($seat['price_id']) && $seat['price_id'] )
    {
      $wip->price_name = NULL;
      $wip->price_id = $seat['price_id'];
    }
    else
    {
      $wip->price_name = sfConfig::get('app_tickets_wip_price', 'WIP');
      $wip->price_id = NULL;
    }
    
    if ( !$wip->trySave() )
      return $this->jsonError('An error occured saving the price for ticket #'.$wip->id.'. '.$cpt.' ticket(s) were successfull.');
    $cpt++;
  }
  
  return 'Success';
