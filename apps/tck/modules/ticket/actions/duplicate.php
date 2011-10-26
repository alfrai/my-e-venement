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
      if ( !is_null($ticket->duplicate) )
      {
        $this->getUser()->setFlash('error',__("Can't cancel the ticket #%%i%% because it is a duplicated ticket... Simply try to cancel the last duplicate of the series",array('%%i%%' => $ticket->id)));
        $this->redirect('ticket/sell?id='.$ticket->transaction_id);
      }
      
      // linking a new cancel ticket to this transaction
      $this->ticket = $ticket->copy();
      $this->ticket->printed = false;
      $this->transaction->Tickets[] = $this->ticket;
      $this->transaction->save();
      
      $ticket->duplicate = $this->ticket->id;
      $ticket->save();
    }
