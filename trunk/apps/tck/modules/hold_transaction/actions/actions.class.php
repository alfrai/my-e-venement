<?php

require_once dirname(__FILE__).'/../lib/hold_transactionGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/hold_transactionGeneratorHelper.class.php';

/**
 * hold_transaction actions.
 *
 * @package    e-venement
 * @subpackage hold_transaction
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class hold_transactionActions extends autoHold_transactionActions
{
  public function executeWaitingList(sfWebRequest $request)
  {
    $this->executeShow($request);
    $this->redirect('hold_transaction/index?hold_id='.$this->hold_transaction->hold_id);
  }
  public function executeAddContact(sfWebRequest $request)
  {
    $transaction = $request->getParameter('transaction');
    $this->forward404Unless($this->hold_transaction = Doctrine::getTable('HoldTransaction')->find($request->getParameter('id')));
    
    if ( is_array($transaction['contact_id']) && ($cid = array_pop($transaction['contact_id'])) )
    {
      $this->forward404Unless($contact = Doctrine::getTable('Contact')->find($cid));
      $this->hold_transaction->Transaction->Contact = $contact;
      $this->hold_transaction->Transaction->professional_id = NULL;
    }
    else
    {
      $this->hold_transaction->Transaction->contact_id = NULL;
      $this->hold_transaction->Transaction->professional_id = NULL;
    }
    $this->hold_transaction->Transaction->save();
  }
  public function executeAddProfessional(sfWebRequest $request)
  {
    $transaction = $request->getParameter('transaction');
    $this->forward404Unless($this->hold_transaction = Doctrine::getTable('HoldTransaction')->find($request->getParameter('id')));
    
    if ( is_array($transaction['professional_id']) && ($proid = array_pop($transaction['professional_id'])) )
    {
      $this->forward404Unless($pro = Doctrine::getTable('Professional')->find($proid));
      $this->hold_transaction->Transaction->Contact = $pro->Contact;
      $this->hold_transaction->Transaction->Professional = $pro;
    }
    else
    {
      $this->hold_transaction->Transaction->professional_id = NULL;
    }
    
    $this->hold_transaction->Transaction->save();
  }
  
  public function executeDump(sfWebRequest $request, $redirect = true)
  {
    $this->getContext()->getConfiguration()->loadHelpers(array('CrossAppLink', 'I18N'));
    $filters = $this->getFilters();
    
    if (!( isset($filters['hold_id']) && $filters['hold_id'] ))
    {
      $this->getUser()->setFlash('error', __('You have to select a hold before anything.'));
      $this->redirect(cross_app_link_to('event', 'hold/index'));
    }
    
    $q = Doctrine::getTable('Hold')->createQuery('h', true)
      ->andWhere('h.id = ?', $filters['hold_id'])
      ->leftJoin('h.Seats s')
      ->leftJoin('h.HoldTransactions htr')
      ->leftJoin('htr.Transaction t')
      ->leftJoin('t.Tickets tck WITH tck.seat_id IS NOT NULL AND tck.manifestation_id = h.manifestation_id AND tck.auto_by_hold = ?', false)
      
      ->leftJoin('h.Next n')
      ->leftJoin('n.HoldTransactions nht')
      
      ->orderBy('htr.rank, htr.id')
    ;
    $hold = $q->fetchOne();
    if ( !$hold->next )
    {
      if ( $redirect )
      {
        $this->getUser()->setFlash('error', __('You have nowhere to dump this waiting list.'));
        $this->redirect('hold_transaction/index');
      }
      return 'Success';
    }
    
    $cpt = 0;
    $free = $hold->Seats->count();
    
    $min = count($hold->Next->HoldTransactions->toKeyValueArray('id', 'rank')) > 0 ? min($hold->Next->HoldTransactions->toKeyValueArray('id', 'rank')) : 0;
    foreach ( $hold->HoldTransactions as $ht )
    {
      $nb = $ht->pretickets - $ht->Transaction->Tickets->count();
      if ( $nb < 0 )
        $nb = 0;
      
      if ( $free >= $nb )
      {
        if ( sfConfig::get('sf_web_debug', false) )
          error_log('Transaction keeped: #'.$ht->transaction_id.' for Hold: '.$hold);
        $free -= $nb;
        continue;
      }
      
      if ( sfConfig::get('sf_web_debug', false) )
        error_log('Transaction moved: #'.$ht->transaction_id.' for Hold: '.$hold);
      
      $hold->Next->HoldTransactions[] = $ht;
      $ht->rank = $min - 1000000 + 1000*$cpt;
      $ht->save();
      $cpt++;
    }
    
    if ( $redirect )
    {
      $this->getUser()->setFlash('notice', __('%%cpt%% transaction(s) have been dumped into this hold.', array('%%cpt%%' => $cpt)));
      $filters['hold_id'] = $hold->next;
      $this->setFilters($filters);
      $this->redirect('hold_transaction/index');
    }
  }
  
  public function executeSeat(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers(array('CrossAppLink', 'I18N'));
    $filters = $this->getFilters();
    
    if (!( isset($filters['hold_id']) && $filters['hold_id'] ))
    {
      $this->getUser()->setFlash('error', __('You have to select a hold before anything.'));
      $this->redirect(cross_app_link_to('event', 'hold/index'));
    }
    
    // 4 levels maximum
    if ( !isset($this->cpt) )
    {
      $this->cpt = 0;
      $this->hold_id = $filters['hold_id'];
    }
    $notice = __('The content of this hold has been seated, as much as it was possible.');
    if ( $this->cpt > 3 )
    {
      $filters['hold_id'] = $this->hold_id;
      $this->setFilters($filters);
      $this->getUser()->setFlash('notice', $notice);
      $this->redirect('hold_transaction/index');
    }
    $this->cpt++;
    
    // flush the previously auto-added tickets
    $this->executeFlush($request, false);
    
    $q = Doctrine::getTable('Hold')->createQuery('h', true)
      ->andWhere('h.id = ?', $filters['hold_id'])
      ->leftJoin('h.HoldTransactions htr')
      ->leftJoin('htr.Transaction t')
      ->leftJoin('t.Tickets tck WITH tck.seat_id IS NOT NULL')
      ->leftJoin('h.Seats s WITH s.id != tck.seat_id')
      ->orderBy('htr.rank, htr.id, s.rank, s.name')
    ;
    $hold = $q->fetchOne();
    $stop = 0;
    foreach ( $hold->HoldTransactions as $ht )
    {
      if ( sfConfig::get('sf_web_debug', false) )
        error_log('Transaction: #'.$ht->transaction_id.' for Hold: '.$hold);
      
      $seater = new Seater(NULL, $hold);
      if ( ($qty = $ht->pretickets - $ht->Transaction->Tickets->count()) <= 0 )
        continue;
      
      if ( sfConfig::get('sf_web_debug', false) )
        error_log('Processing Transaction: #'.$ht->transaction_id.' for Hold: '.$hold);
      
      $seats = $seater->findSeats($qty);
      if ( $seats->count() == 0 )
      {
        for ( $i = 2 ; $i <= $qty && sfConfig::get('app_holds_can_divide_demands', true) ; $i++ )
        {
          if ( sfConfig::get('sf_web_debug', false) )
            error_log('No enough seats found for '.($i-1).' group(s). Trying to divide the group in '.$i.' groups ('.($i-1).' x '.ceil($qty/$i).' + '.(ceil($qty/$i) - $qty%$i).').');
          
          // try to seat $i groups individually
          $seats = new Doctrine_Collection('Seat');
          for ( $j = 0 ; $j < $i - 1 ; $j++ )
            $seats->merge($seater->findSeats(ceil($qty/$i), $seats));
          $seats->merge($seater->findSeats(ceil($qty/$i) - $qty%$i, $seats));
          
          if ( sfConfig::get('sf_web_debug', false) )
            error_log("For $i groups, ".$seats->count()." seats have been found");
          
          if ( $seats->count() == $qty )
            break;
        }
      }
      
      if ( sfConfig::get('sf_web_debug', false) )
        error_log('Seats found: '.$seats->count().' for expected quantity: '.$qty);
      
      if ( $seats->count() == $qty )
      {
        foreach ( $seats as $seat )
        {
          if ( sfConfig::get('sf_web_debug', false) )
            error_log('new ticket for seat: '.$seat);
          
          $ticket = new Ticket;
          $ticket->Seat = $seat;
          $ticket->auto_by_hold = true;
          $ticket->manifestation_id = $hold->manifestation_id;
          error_log('with price '.$hold->price_id);
          if ( $hold->price_id )
          {
            error_log('with price '.$hold->price_id);
            $ticket->price_id = $hold->price_id;
          }
          else
          {
            $ticket->value = 0;
            $ticket->price_name = sfConfig::get('app_tickets_wip_price', 'WIP');
          }
          $ht->Transaction->Tickets[] = $ticket;
        }
        if ( sfConfig::get('sf_web_debug', false) )
          error_log('Transaction #'.$ht->transaction_id.' has been processed with '.$ht->Transaction->Tickets->count().' tickets.');
        $ht->Transaction->save();
      }
      else
        $stop++;
    }
    
    // dump the rest of the hold into the next one, and seat what is possible
    if ( $hold->next )
    {
      // dump
      $this->executeDump($request, false);
      
      // seat
      $filters['hold_id'] = $hold->next;
      $this->setFilters($filters);
      $this->executeSeat($request);
    }
    
    $filters['hold_id'] = $this->hold_id;
    $this->setFilters($filters);
    $this->getUser()->setFlash('notice', $notice);
    $this->redirect('hold_transaction/index');
  }
  public function executeFlush(sfWebRequest $request, $redirect = true)
  {
    $this->getContext()->getConfiguration()->loadHelpers(array('I18N'));
    $filters = $this->getFilters();
    
    if (!( isset($filters['hold_id']) && $filters['hold_id'] ))
    {
      $this->getUser()->setFlash('error', __('You have to select a hold before anything.'));
      $this->redirect(cross_app_link_to('event', 'hold/index'));
    }
    
    $q = Doctrine_Query::create()->from('Ticket tck')
      ->andWhere('tck.seat_id IS NOT NULL')
      ->andWhere('tck.auto_by_hold = ?', true)
      ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL')
      ->andWhere('tck.transaction_id IN (SELECT ht.transaction_id FROM HoldTransaction ht WHERE ht.hold_id = ?)', $filters['hold_id'])
    ;
    $q->delete()->execute();
    
    if ( $redirect )
      $this->redirect('hold_transaction/index');
  }
  
  public function executeBack(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('CrossAppLink');
    $filters = $this->getFilters();
    
    if (!( isset($filters['hold_id']) && $filters['hold_id'] ))
      $this->redirect(cross_app_url_for('event', 'hold/index'));
    $this->redirect(cross_app_url_for('event', 'hold/edit?id='.$filters['hold_id']));
  }
  
  public function executePlus(sfWebRequest $request)
  {
    $this->executeShow($request);
    $this->hold_transaction->pretickets++;
    $this->hold_transaction->save();
    
    $this->json = array();
    $this->json['id'] = $this->hold_transaction->id;
    $this->json['quantity'] = $this->hold_transaction->pretickets;
  }
  public function executeMinus(sfWebRequest $request)
  {
    $this->executeShow($request);
    if ( $this->hold_transaction->pretickets > 0 )
    {
      $this->hold_transaction->pretickets--;
      $this->hold_transaction->save();
    }
    
    $this->json = array();
    $this->json['id'] = $this->hold_transaction->id;
    $this->json['quantity'] = $this->hold_transaction->pretickets;
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    $filters = $this->getFilters();
    $old_id = false;
    
    if ( $next_to = $request->getParameter('next_to', false) )
    {
      $this->forward404Unless($tmp = Doctrine::getTable('Hold')->createQuery('h')
        ->leftJoin('h.Feeders f')
        ->andWhere('f.id = ?', $next_to)
        ->fetchOne());
      $old_id = $filters['hold_id'];
      $request->setParameter('hold_id', $tmp->id);
    }
    
    if ( $hold_id = $request->getParameter('hold_id') )
    {
      $filters['hold_id'] = $hold_id;
      $this->setFilters($filters);
    }
    
    if ( !isset($filters['hold_id']) )
    {
      $this->getContext()->getConfiguration()->loadHelpers('CrossAppLink');
      $this->redirect(cross_app_url_for('event', 'hold/index'));
    }
    
    sfConfig::set('module_hold_id', $filters['hold_id']);
    parent::executeIndex($request);
    
    // re-establishing the old parameter, for any refresh
    if ( $old_id )
    {
      $filters['hold_id'] = $old_id;
      $this->setFilters($filters);
    }
  }
  
  public function executeEdit(sfWebRequest $request)
  {
    parent::executeEdit($request);
    if ( $this->hold_transaction->Transaction->closed && $this->getUser()->hasCredential('tck-unblock') )
    {
      $this->hold_transaction->Transaction->closed = false;
      $this->hold_transaction->save();
    }
    $this->redirect('transaction/edit?id='.$this->hold_transaction->transaction_id.'#manifestations-'.$this->hold_transaction->Hold->manifestation_id);
  }
  public function executeNew(sfWebRequest $request)
  {
    $filters = $this->getFilters();
    if ( isset($filters['hold_id']) && $filters['hold_id'] )
    {
      $ht = new HoldTransaction;
      $ht->hold_id = $filters['hold_id'];
      $ht->save();
      $this->getUser()->setFlash('success', 'The item was created successfully.');
      $this->redirect('hold_transaction/index');
    }
    
    parent::executeNew();
  }
  
  public function executeChangeRank(sfWebRequest $request)
  {
    foreach ( $list = array('smaller_than' => 0, 'bigger_than' => 0, 'id' => 0,) as $key => $value )
    if ( intval($request->getParameter($key)).'' === ''.$request->getParameter($key) )
      $list[$key] = intval($request->getParameter($key));
    else
      unset($list[$key]);
    $this->forward404Unless(isset($list['id']));
    
    $this->hold_transactions = Doctrine::getTable('HoldTransaction')->createQuery('ht')
      ->leftJoin('ht.Hold h')
      ->leftJoin('ht.Transaction t')
      ->leftJoin('t.Tickets tck WITH tck.seat_id IS NOT NULL AND tck.manifestation_id = h.manifestation_id')
      ->andWhere('h.id = (SELECT hht.hold_id FROM HoldTransaction hht WHERE hht.id = ?)', $list['id'])
      ->execute();
    
    $holds = $request->getParameter('hold');
    foreach ( array('previous' => 'bigger_than', 'next' => 'smaller_than') as $field => $rank )
    {
      if ( !isset($list[$rank]) )
      {
        $holds[$field] = $this->hold_transactions[0]->hold_id;
        continue;
      }
      $this->forward404Unless(isset($holds[$field]) && intval($holds[$field]).'' === ''.$holds[$field]);
      $holds[$field] = intval($holds[$field]);
    }
    
    // if no HoldTransaction exists for the given id
    $this->forward404Unless( count($list) > 1 && in_array($list['id'], $this->hold_transactions->getPrimaryKeys()) );
    
    $ranks = $this->hold_transactions->toKeyValueArray('id', 'rank');
    foreach ( $this->hold_transactions as $ht )
    if ( $ht->id == $list['id'] )
    {
      $this->hold_transaction = $ht;
      break;
    }
    
    $this->reload = false;
    
    // 1. if the previous hold is the same than the current one, but the next hold is different... check if there are available seats
    if ( $ht->hold_id == $holds['previous'] && $ht->hold_id != $holds['next'] )
    {
      $nb = $ht->pretickets > $ht->Transaction->Tickets->count() ? $ht->pretickets : $ht->Transaction->Tickets->count();
      $free = $ht->Hold->getNbFreeSeats($this->hold_transactions);
      
      if (!( $free !== false && $free >= $nb ))
      {
        // case where there is not enough seat in the current hold
        $ht->Hold = $ht->Hold->Next;
        $this->reload = true;
        $ht->rank = $ht->Hold->getMinRank()/2;
        $ht->save();
        return 'Success';
      }
    }
    // 2. if the previous hold and the next hold are different from the current one, but both are the same
    elseif ( $ht->hold_id != $holds['previous'] && $ht->hold_id != $holds['next'] && $holds['next'] == $holds['previous'] )
    {
      $ht->hold_id = $holds['previous'];
      $this->reload = true;
      $hts = Doctrine::getTable('HoldTransaction')->createQuery('ht')
        ->andWhereIn('ht.id', array($list['smaller_than'], $list['bigger_than']))
        ->orderBy('ht.rank')
        ->execute();
      $ht->rank = $hts[1]->rank + ($hts[0]->rank - $hts[1]->rank)/2;
      $ht->save();
      return 'Success';
    }
    // 3. all holds are differents, taking in count the "previous" hold
    elseif ( $ht->hold_id != $holds['previous'] && $ht->hold_id != $holds['next'] && $holds['next'] != $holds['previous'] )
    {
      $ht->hold_id = $holds['previous'];
      $this->reload = true;
      $hts = Doctrine::getTable('HoldTransaction')->createQuery('ht')
        ->andWhereIn('ht.id', array($list['smaller_than']))
        ->orderBy('ht.rank')
        ->execute();
      $ht->rank = $hts[0]->rank*2;
      $ht->save();
      return 'Success';
    }
    // 4. the current transaction is going upper than the best transaction of its own Hold
    elseif ( $ht->hold_id != $holds['previous'] && $ht->hold_id == $holds['next'] )
    {
      $ht->rank = $ht->Hold->getMinRank()/2;
      $ht->save();
      return 'Success';
    }
    
    error_log('after');
    if ( !isset($list['bigger_than']) )
      $ht->rank = $ranks[$list['smaller_than']]/2;
    elseif ( !isset($list['smaller_than']) )
      $ht->rank = $ranks[$list['bigger_than']]*2;
    else
      $ht->rank = $ranks[$list['bigger_than']] + ($ranks[$list['smaller_than']] - $ranks[$list['bigger_than']])/2;
    $ht->save();
  }
  
  protected function buildQuery()
  {
    $q = parent::buildQuery();
    $ht = $q->getRootAlias();
    $q->leftJoin("$ht.Transaction t")
      ->leftJoin('t.Tickets tck WITH tck.duplicating IS NULL AND tck.cancelling IS NULL')
    ;
    return $q;
  }
}
