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
  
  // deleting all payments
  $q = new Doctrine_Query();
  $q->from('Payment p')
    ->andWhereIn('p.transaction_id',array($tid,$transaction->transaction_id))
    ->delete()
    ->execute();
  
  // deleting integrated tickets
  $q = new Doctrine_Query;
  $q->from('Ticket tck')
    ->andWhere('tck.transaction_id = ?',$tid)
    ->andWhere('tck.integrated = true')
    ->andWhere('tck.printed = false')
    ->andWhere('tck.id NOT IN (SELECT t2.cancelling FROM ticket t2)')
    ->delete()
    ->execute();
  
  // cancelling printed tickets
  $q = new Doctrine_Query;
  $value = 0;
  $tickets = $q->from('Ticket tck')
    ->andWhere('tck.transaction_id = ?',$tid)
    ->andWhere('tck.printed = true')
    ->andWhere('(SELECT count(*) FROM ticket t2 WHERE t2.cancelling = tck.id) = 0')
    ->execute();
  
  $translinked = is_null($transaction->transaction_id)
    ? new Transaction
    : Doctrine::getTable('Transaction')->findOneById($transaction->transaction_id);
  $translinked->type = 'cancellation';
  $translinked->transaction_id = $transaction->id;
  $translinked->contact_id = $transaction->contact_id;
  $translinked->professional_id = $transaction->professional_id;
  
  $value = 0;
  if ( $tickets->count() > 0 )
  {
    foreach ( $tickets as $ticket )
    {
      $cancel = $ticket->copy();
      $cancel->id =
      $cancel->transaction_id =
      $cancel->sf_guard_user_id =
      $cancel->created_at =
      $cancel->updated_at = NULL;
      $cancel->printed = $cancel->integrated = false;
      $cancel->value = -$cancel->value;
      $cancel->cancelling = $ticket->id;
      $translinked->Tickets[] = $cancel;
      $value += $ticket->value;
    }
  }
  
  // add payments
  $payment = new Payment;
  $payment->value = -$value;
  $payment->payment_method_id = $pid;
  $transaction->Payments[] = $payment;
  
  $payment = new Payment;
  $payment->value = $value;
  $payment->payment_method_id = $pid;
  $translinked->Payments[] = $payment;
    
  // saving the transactions
  $translinked->save();
  $transaction->transaction_id = $translinked->id;
  $transaction->save();
  
  // get out
  $this->getUser()->setFlash('notice',__('Your transaction has been correctly cancelled'));
  $this->redirect('ticket/cancel?pay='.$transaction->transaction_id);
  return sfView::NONE;
