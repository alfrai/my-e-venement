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
  
  $price_name = $price_id = $manifestation_id = $gauge_id = NULL;
  
  // compatibility w/ the new ticketting process
  if ( $params = $request->getParameter('transaction') )
  {
    $tid = $params['price_new']['id'];
    $price_id = $params['price_new']['price_id'];
    $qty = $params['price_new']['qty'];
    $qty = intval($qty) > 0 ? $qty : 1;
    $gauge_id = $params['price_new']['gauge_id'];
  }
  else
  {
    $tid = $request->getParameter('id');
    $price_name = $request->getParameter('price_name');
    $qty = $request->getParameter('qty');
    $manifestation_id = $request->getParameter('manifestation_id');
  }
  
  if ( intval($tid).'' !== ''.$tid || intval($qty).'' !== ''.$qty
    || intval($manifestation_id).'' !== ''.$manifestation_id && intval($gauge_id).'' !== ''.$gauge_id
    || !$price_name && !$price_id )
  {
    $this->getUser()->setFlash('error',__('Error reading the given values'));
    $this->redirect('ticket/cancel');
  }
  
  $q = Doctrine_Query::create()
    ->from('Ticket tck')
    ->leftJoin('tck.Transaction t')
    ->leftJoin('tck.Price p')
    ->leftJoin('t.Translinked t2')
    ->andWhere('t.id = ?',$tid)
    ->andWhere('t.closed = ?', false)
    ->andWhere('tck.printed_at IS NOT NULL')
    ->andWhere('tck.cancelling IS NULL')
    ->andWhere('tck.duplicating IS NULL')
    ->andWhere('tck.id NOT IN (SELECT tck2.cancelling FROM Ticket tck2 WHERE tck2.cancelling IS NOT NULL)')
    ->limit($qty);
  
  if ( $price_id )
    $q->andWhere('p.id = ?',$price_id);
  else
    $q->andWhere('LOWER(p.name) = LOWER(?)',$price_name);
  
  if ( $gauge_id )
    $q->andWhere('tck.gauge_id = ?',$gauge_id);
  else
    $q->andWhere('tck.manifestation_id = ?',$manifestation_id);
  
  $tickets = $q->execute();
  
  if ( $tickets->count() == 0 )
  {
    $this->getUser()->setFlash('error',__('There is no ticket available for cancellation corresponding to your criterias'));
    return $this->redirect('ticket/cancel');
  }
  
  $transaction = $tickets[0]->Transaction;
  
  if (( ($transaction->closed && !$this->getUser()->hasCredential('tck-unblock'))
    || ($this->getUser()->hasCredential('tck-control'))
    ) && !$this->getUser()->isSuperAdmin() )
  {
    $this->getUser()->setFlash('error',__('Oops! The screen you asked for is secure and you do not have proper credentials.','sf_admin',array()));
    $this->redirect('ticket/cancel');
  }
  
  if ( $transaction->Translinked->count() == 0 )
  {
    $transaction->Translinked[] = new Transaction;
    $transaction->updated_at = NULL;
    $transaction->Translinked[0]->type = 'cancellation';
    $transaction->sf_guard_user_id = NULL;
    $transaction->Translinked[0]->contact_id = $transaction->contact_id;
    $transaction->Translinked[0]->professional_id = $transaction->professional_id;
    $transaction->save();
  }
  
  // cancelling tickets
  foreach ( $tickets as $ticket )
  if ( !$ticket->hasBeenCancelled() )
  {
    $cancel = $ticket->copy();
    $cancel->value = -$cancel->value;
    $cancel->cancelling = $ticket->id;
    $cancel->id = $cancel->duplicating = $cancel->transaction_id = $cancel->sf_guard_user_id = NULL;
    $cancel->created_at = $cancel->updated_at = NULL;
    $cancel->printed_at = $cancel->integrated_at = NULL;
    $cancel->transaction_id = $transaction->Translinked[0]->id;
    $cancel->save();
  }
  
  $transaction->save();
  
  // get out
  $this->getUser()->setFlash('notice',__('%%nb%% ticket(s) have been correctly cancelled, with transaction #%%tid%%',array('%%nb%%' => $tickets->count(), '%%tid%%' => $transaction->Translinked[0]->id)));
  $this->redirect('ticket/cancel?pay='.$transaction->Translinked[0]->id);
  return sfView::NONE;
