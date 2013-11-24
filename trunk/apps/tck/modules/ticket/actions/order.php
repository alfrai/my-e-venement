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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $this->executeAccounting($request,false);
    $this->order = $this->transaction->Order[0];
    
    if ( $request->hasParameter('cancel-order') )
    {
      $this->order->delete();
      foreach ( $this->transaction->Tickets as $ticket )
      {
        // OPTIMIZATION NEEDED
        $ticket->numerotation = NULL;
        $ticket->save();
      }
      
      return sfView::NONE;
    }
    
    // saving the order, transforms common tickets into booked tickets
    if ( is_null($this->order->id) )
      $this->order->save();

    // checks if any tickets need a seat
    foreach ( $this->transaction->Tickets as $ticket )
    if ( !$ticket->numerotation
      && $ticket->Gauge->Workspace->seated
      && $seated_plan = $ticket->Manifestation->Location->getWorkspaceSeatedPlan($ticket->Gauge->workspace_id)
    )
    {
      // if so ask the user which one to use for this ticket
      sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N','Url'));
      $this->getUser()->setFlash('notice', __('You still have to give some tickets a seat...'));
      return $this->redirect(url_for('ticket/placing?id='.$this->transaction->id.'&gauge_id='.$ticket->gauge_id));
    }
    
    // if everything's ok, prints out the order
    return 'Success';
