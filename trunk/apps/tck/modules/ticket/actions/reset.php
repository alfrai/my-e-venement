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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $this->getContext()->getConfiguration()->loadHelpers(array('I18N'));
    
    // with '$this->transaction = $this->getRoute()->getObject();' it updates the tickets updated_at information........
    $q = new Doctrine_Query;
    $q->from('Transaction t')
      ->select('t.*')
      ->leftJoin('t.Invoice i')
      ->addSelect('(SELECT count(tck.id) FROM Ticket tck WHERE tck.transaction_id = t.id) AS nb_tickets')
      ->leftJoin('t.Tickets tck2 WITH tck2.printed_at IS NULL') // integrated tickets can be deleted
      ->where('t.id = ?',$request->getParameter('id'));
    $this->transaction = $q->fetchOne();
    
    $toprint = $this->transaction->getNotPrinted();
    
    if ( $toprint != $this->transaction->nb_tickets || $this->transaction->Invoice->count() > 0 )
    {
      $this->getUser()->setFlash('error',__("Resetting this transaction is not allowed, it contains printed tickets or invoices..."));
      return $this->redirect('ticket/sell?id='.$this->transaction->id);
    }
    
    foreach ( array('Tickets', 'Order', 'Payments') as $rel )
      $this->transaction->{$rel}->delete();
    
    $this->getUser()->setFlash('notice',__('Transaction resetted and closed'));
    $this->transaction->closed = true;
    $this->transaction->save();
    
    return $this->redirect('ticket/closed?id='.$this->transaction->id);
