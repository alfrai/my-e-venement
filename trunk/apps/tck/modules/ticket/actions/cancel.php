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
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
    
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
      if ( !$ticket->printed )
      {
        $this->getUser()->setFlash('error',__("Can't cancel the ticket #%%i%% because it was not yet printed... Just try to suppress it",array('%%i%%' => $ticket->id)));
        $this->redirect('ticket/sell?id='.$ticket->transaction_id);
      }
      
      // get back a potential existing transaction
      $transactions = Doctrine::getTable('Transaction')->createQuery('t')
        ->andWhere('t.transaction_id = ?',$ticket->transaction_id)
        ->execute();
      if ( $transactions->count() > 0 )
        $this->transaction = $transactions[0];
      else
      {
        // or create one
        $this->transaction = new Transaction();
        $this->transaction->type = 'cancellation';
        $this->transaction->transaction_id = $ticket->transaction_id;
      }
      
      // get back a potential cancellation ticket for this ticket_id
      $q = Doctrine::getTable('Ticket')->createQuery('t')
        ->andWhere('cancelling = ?',$ticket->id)
        ->orderBy('id DESC')
        ->limit(1);
      $duplicatas = $q->execute();
      
      // linking a new cancel ticket to this transaction
      $this->ticket = $ticket->copy();
      $this->ticket->cancelling = $ticket->id;
      $this->ticket->printed = false;
      $this->ticket->value = -$this->ticket->value;
      $this->transaction->Tickets[] = $this->ticket;
      $this->transaction->save();
      
      // saving the old ticket for duplication
      if ( $duplicatas->count() > 0 )
      {
        $duplicatas[0]->duplicate = $this->ticket->id;
        $duplicatas[0]->save();
      }
      
      // printing
      $this->getUser()->setFlash('notice','Ticket canceled.');
      $this->setTemplate('canceledTicket');
    }
    else
      $this->executeCancelBoot($request);
