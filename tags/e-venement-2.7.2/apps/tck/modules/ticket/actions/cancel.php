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
    
    $tmp = explode(',',$request->getParameter('ticket_id'));
    $ticket_ids = array(0);
    foreach ( $tmp as $key => $ids )
    if ( $ids )
    {
      $ids = explode('-',$ids);
      if ( !isset($ids[1]) ) $ids[1] = intval($ids[0]);
      for ( $i = intval($ids[0]) ; $i <= $ids[1] ; $i++ )
        $ticket_ids[$i] = $i;
    }
    
    foreach ( $tickets = Doctrine::getTable('Ticket')
      ->createQuery('tck')
      ->leftJoin('tck.Transaction t')
      ->leftJoin('t.Translinked t2')
      ->leftJoin('tck.Duplicated dup')
      ->leftJoin('dup.Duplicated dup2')
      ->andWhereIn('tck.id',$ticket_ids)
      ->execute() as $ticket )
    {
      if ( !$ticket )
      {
        $this->getUser()->setFlash('error',__("Can't find the ticket #%%i%% in database... Perhaps the related transaction is closed already.",array('%%i%%' => $ticket->id)));
        $this->redirect('ticket/cancel');
      }
      if ( $ticket->Transaction->closed )
      {
        $this->getUser()->setFlash('error',__("Cannot cancel a ticket #%%i%% because its transaction #%%t%% is already closed. Trick: open its transaction in an other tab when you try to cancel it.",array('%%i%%' => $ticket->id, '%%t%%' => $ticket->transaction_id)));
        $this->redirect('ticket/cancel');
      }
      if ( !$ticket->printed_at )
      {
        $this->getUser()->setFlash('error',__("Cannot cancel the ticket #%%i%% because it has not yet been printed... Just try to suppress it",array('%%i%%' => $ticket->id)));
        $this->redirect('transaction/edit?id='.$ticket->transaction_id);
      }
      if ( $ticket->Duplicatas->count() != 0 )
      {
        $this->getUser()->setFlash('error',__("Can't cancel the ticket #%%i%% because it is a duplicated ticket... Simply try to cancel the last duplicate of the series",array('%%i%%' => $ticket->id)));
        $this->redirect('transaction/edit?id='.$ticket->transaction_id);
      }
      if ( !is_null($ticket->cancelling) )
      {
        $this->getUser()->setFlash('error',__("Can't cancel the ticket #%%i%% because it is already a cancellation",array('%%i%%' => $ticket->id)));
        $this->redirect('ticket/pay?id='.$ticket->transaction_id);
      }
      
      // get back a potential existing transaction
      $transactions = $ticket->Transaction->Translinked;
      if ( $transactions->count() > 0 )
        $this->transaction = $transactions[0];
      else
      {
        // or create one
        $this->transaction = new Transaction();
        $this->transaction->type = 'cancellation';
        $this->transaction->transaction_id = $ticket->transaction_id;
        $ticket->Transaction->Translinked[] = $this->transaction;
        // link the cancelling transaction to the previous contact/professional
        $this->transaction->contact_id = $ticket->Transaction->contact_id;
        $this->transaction->professional_id = $ticket->Transaction->professional_id;
      }
      
      // get back a potential cancellation ticket for this ticket_id
      $q = Doctrine::getTable('Ticket')->createQuery('t')
        ->andWhere('t.cancelling = ?',$ticket->getOriginal()->id)
        ->orderBy('t.id DESC');
      $orig = $q->fetchOne();
      
      // linking a new cancel ticket to this transaction
      $this->ticket = $ticket->copy();
      $this->ticket->cancelling = $ticket->getOriginal()->id;
      $this->ticket->duplicating = NULL;
      $this->ticket->printed_at = NULL;
      $this->ticket->value = -$this->ticket->value;
      $this->transaction->Tickets[] = $this->ticket;
      
      // saving the old ticket for duplication
      if ( $orig )
        $this->ticket->duplicating = $orig->id;
      
      $this->transaction->save();
      
      // printing
      $this->getUser()->setFlash('notice',__('Ticket canceled.'));
      $this->setTemplate('canceledTicket');
    }
    
    if ( $tickets->count() == 0 )
    {
      if ( $request->hasParameter('ticket_id') )
        $this->getUser()->setFlash('error',__("Can't find the ticket #%%i%% in database...",array('%%i%%' => $request->getParameter('ticket_id'))));
      $this->executeCancelBoot($request);
    }
