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
  $this->getContext()->getConfiguration()->loadHelpers('I18N');
  
  $tid = $request->getParameter('id');
  $pid = $request->getParameter('payment_method_id');
  
  if ( intval($tid).'' !== ''.$tid || intval($pid).'' !== ''.$pid )
  {
    $this->getUser()->setFlash('error',__('Error reading the given values'));
    $this->redirect('ticket/cancel');
  }
  
  $transaction = Doctrine::getTable('Transaction')->findOneById($tid);
  if ( $transaction->closed && !$this->getUser()->hasCredential('tck-unblock') )
  {
    $this->getUser()->setFlash('error',__('Oops! The screen you asked for is secure and you do not have proper credentials.','sf_admin',array()));
    $this->redirect('ticket/cancel');
  }
  $translinked = $transaction->Translinked->count() > 0
    ? $transaction->Translinked[0]
    : new Transaction;
  $translinked->type = 'cancellation';
  $translinked->transaction_id = $transaction->id;
  $translinked->contact_id = $transaction->contact_id;
  $translinked->professional_id = $transaction->professional_id;
  
  // deleting all payments (including payments from previous cancelling transactions)
  $tids = array($tid,$transaction->transaction_id);
  foreach ( $transaction->Translinked as $tl )
    $tids[] = $tl->id;
  $q = new Doctrine_Query();
  $q->from('Payment p')
    ->andWhereIn('p.transaction_id',$tids)
    ->delete()
    ->execute();
  
  // deleting integrated tickets
  $q = new Doctrine_Query;
  $q->from('Ticket tck')
    ->andWhere('tck.transaction_id = ?',$tid)
    ->andWhere('tck.integrated_at IS NOT NULL AND tck.printed_at IS NULL')
    ->andWhere('tck.id NOT IN (SELECT t2.cancelling FROM ticket t2)')
    ->delete()
    ->execute();
  
  // cancelling printed tickets
  $q = new Doctrine_Query;
  $value = 0;
  $tickets = $q->from('Ticket tck')
    ->leftJoin('tck.Transaction t')
    ->leftJoin('tck.Cancelled cancel')
    ->leftJoin('tck.Duplicatas dup')
    ->leftJoin('dup.Cancelled cancel2')
    ->andWhere('tck.duplicating IS NULL')
    ->andWhere('tck.cancelling IS NULL')
    ->andWhere('tck.transaction_id = ?',$tid)
    ->andWhere('t.closed = ?',false)
    ->andWhere('tck.printed_at IS NOT NULL')
    ->execute();
  
  $value = 0;
  if ( $tickets->count() > 0 )
  {
    foreach ( $tickets as $ticket )
    {
      if ( !$ticket->hasBeenCancelled() )
      {
        $cancel = $ticket->copy();
        $cancel->id =
        $cancel->duplicating =
        $cancel->transaction_id =
        $cancel->sf_guard_user_id =
        $cancel->created_at = $cancel->updated_at = NULL;
        $cancel->printed_at = $cancel->integrated_at = NULL;
        $cancel->value = -$cancel->value;
        $cancel->cancelling = $ticket->id;
        $translinked->Tickets[] = $cancel;
      }
      $value += $ticket->value;
    }
  }
  
  // add payments
  $payment = new Payment;
  $payment->value = $value;
  $payment->payment_method_id = $pid;
  $payment->transaction_id = $transaction->id;
  $payment->save();
  
  $payment = new Payment;
  $payment->value = -$value;
  $payment->payment_method_id = $pid;
  $translinked->Payments[] = $payment;
    
  // saving the transactions
  $translinked->save();
  
  // get out
  $this->getUser()->setFlash('notice',__('Your transaction has been correctly cancelled'));
  $this->redirect('ticket/cancel?pay='.$translinked->id);
  return sfView::NONE;
