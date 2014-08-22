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
    $vel = sfConfig::get('app_tickets_vel');
    if ( !isset($vel['full_seating_by_customer']) ) $vel['full_seating_by_customer'] = false;
    
    $this->json = array(
      'error' => false,
      'success' => false,
    );
    
    if (!( $vel['full_seating_by_customer'] && sfConfig::get('app_tickets_wip_price', 'WIP') ))
      return $this->jsonError('This plateform does not allow this action', $request);
    
    $ticket = Doctrine_Query::create()->from('Ticket tck')
      ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL')
      ->andWhere('tck.gauge_id = ?', $request->getParameter('id'))
      ->andWhere('tck.seat_id = ?', $request->getParameter('seat_id'))
      ->andWhere('tck.transaction_id = ?', $this->getUser()->getTransaction()->id)
      ->fetchOne();
    $success = array('deleted' => array(array(
      'ticket_id' => $ticket->id,
      'price_id'  => $ticket->price_id,
      'gauge_id'  => $ticket->gauge_id,
      'price_name'=> $ticket->price_name,
      'seat_id'   => $ticket->seat_id,
    )));
    if ( !$ticket->delete() )
      return $this->jsonError('The given seat cannot be removed, try again', $request);
    
    $this->json['success'] = $success;
    $this->debug($request);
    
    $this->json['success']['orphans'] =
      $this->getContext()->getConfiguration()->getOrphans($this->getUser()->getTransaction(),
        array('gauge_id' => $ticket->gauge_id)
      );
    
    return 'Success';
