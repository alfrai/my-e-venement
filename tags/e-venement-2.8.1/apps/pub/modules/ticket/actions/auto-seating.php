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
    
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->json = array(
      'error' => false,
      'success' => false,
    );
    
    if ( !$vel['full_seating_by_customer'] )
      return $this->jsonError('This plateform does not allow this action', $request);
    
    $this->transaction = $this->getUser()->getTransaction();
    try {
      $this->recordTransaction($request);
    }
    catch ( liSeatedException $e )
    {
      return $this->jsonError(array(
        $e->getMessage(),
        'We are sorry, you will have to choose your seats by yourself.',
      ), $request);
    }
    
    $seats = array();
    foreach ( $this->transaction->Tickets as $ticket )
      $seats[$ticket->gauge_id][$ticket->price_id][$ticket->seat_id] = (string)$ticket->Seat;
    
    $this->json['success'] = array(
      'seats'   => $seats,
    );
    
    $this->debug($request);
    return 'Success';
