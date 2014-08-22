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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $this->getContext()->getConfiguration()->loadHelpers(array('I18N'));
    
    if ( !$request->hasParameter('ticket_id') )
      return sfView::NONE;
    
    $tmp = explode(',',$request->getParameter('ticket_id'));
    $ticket_ids = array();
    foreach ( $tmp as $key => $ids )
    if ( $ids )
    {
      $ids = explode('-',$ids);
      if ( !isset($ids[1]) ) $ids[1] = intval($ids[0]);
      for ( $i = intval($ids[0]) ; $i <= $ids[1] ; $i++ )
        $ticket_ids[$i] = $i;
    }
    
    $this->tickets = array();
    if ( count($ticket_ids) > 0 )
    foreach ( $ticket_ids as $id )
    {
      // get back the ticket to cancel
      $ticket = Doctrine::getTable('Ticket')
        ->findOneById(intval($id));
      if ( !$ticket )
      {
        $this->getUser()->setFlash('error',__("Can't find the ticket #%%i%% in database...",array('%%i%%' => $ticket->id)));
        $this->redirect('ticket/cancel');
      }
      if ( $ticket->Transaction->closed )
      {
        $this->getUser()->setFlash('error',__("Cannot duplicate the ticket #%%i%% because its transaction has been closed already. Trick: open its transaction in an other tab when you try to cancel it.",array('%%i%%' => $ticket->id)));
        $this->redirect('ticket/sell?id='.$ticket->transaction_id);
      }
      if ( !$ticket->printed_at )
      {
        $this->getUser()->setFlash('error',__("Can't duplicate the ticket #%%i%% because it was not yet printed... Just try to print it",array('%%i%%' => $ticket->id)));
        $this->redirect('ticket/sell?id='.$ticket->transaction_id);
      }
      if ( $ticket->Cancelling->count() > 0 )
      {
        $this->getUser()->setFlash('error',__("Can't duplicate the ticket #%%i%% because it has been cancelled already.",array('%%i%%' => $ticket->id)));
        $this->redirect('ticket/sell?id='.$ticket->transaction_id);
      }
      if ( $ticket->Duplicatas->count() > 0 )
      {
        $this->getUser()->setFlash('error',__("Can't duplicate the ticket #%%i%% because it has been already duplicated... Simply try to duplicate the last duplicate of the serie",array('%%i%%' => $ticket->id)));
        $this->redirect('ticket/sell?id='.$ticket->transaction_id);
      }
      if ( $ticket->Controls->count() > 0 )
      {
        $this->getUser()->setFlash('error',__("Sorry, we can't duplicate the ticket #%%i%% because it has been checked already.",array('%%i%%' => $ticket->id)));
        $this->redirect('ticket/sell?id='.$ticket->transaction_id);
      }
      
      // copying the current ticket
      $this->ticket = $ticket->copy();
      
      // removing the numerotation before saving the duplicata
      $ticket->seat_id = NULL;
      $ticket->save();
      
      // creating a duplicata
      $this->ticket->printed_at = date('Y-m-d H:i:s');
      $this->ticket->created_at = NULL;
      $this->ticket->updated_at = NULL;
      $this->ticket->sf_guard_user_id = NULL;
      $this->ticket->id = NULL;
      $this->ticket->duplicating = $ticket->id;
      $this->ticket->save();
      $this->tickets[] = $this->ticket;
    }
    
    $this->duplicate = true;
    $this->setLayout('nude');
    $this->setTemplate('print');
