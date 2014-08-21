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
    
    $ticket = new Ticket;
    $ticket->price_name = sfConfig::get('app_tickets_wip_price', 'WIP');
    $ticket->value = 0;
    $ticket->vat = 0;
    $ticket->seat_id = $request->getParameter('seat_id');
    $ticket->gauge_id = $request->getParameter('id');
    $ticket->transaction_id = $this->getUser()->getTransaction()->id;
    if ( !$ticket->trySave() )
      return $this->jsonError('Maybe this seat is no longer available, check again.');
    
    $this->json['success'] = array(
      'name' => $ticket->price_name,
      'seat_id' => $ticket->seat_id,
      'seat_name' => (string)$ticket->Seat,
    );
    
    $this->debug($request);
    return 'Success';
