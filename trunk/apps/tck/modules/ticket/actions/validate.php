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
    $this->transaction = $this->getRoute()->getObject();
    
    $topay = 0;
    $toprint = 0;
    foreach ( $this->transaction->Tickets as $ticket )
    if ( is_null($ticket->duplicate) )
    {
      $topay += $ticket->value;
      if ( !$ticket->printed )
        $toprint++;
    }
    
    $paid = 0;
    foreach ( $this->transaction->Payments as $payment )
      $paid += $payment->value;
    
    if ( $paid >= $topay && $toprint <= 0 )
    {
      $this->getUser()->setFlash('notice','Transaction validated and closed');
      $this->transaction->closed = true;
      $this->transaction->save();
      return $this->redirect('ticket/closed?id='.$this->transaction->id);
    }
    
    if ( $toprint <= 0 )
      $this->getUser()->setFlash('error','The transaction cannot be validated, please check again the payments...');
    else
      $this->getUser()->setFlash('error','The transaction cannot be validated, there are still tickets to print...');
    
    return $this->redirect('ticket/sell?id='.$this->transaction->id);
