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
  public function executeDump(sfWebRequest $request)
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
      ->leftJoin('h.HoldTransactions htr')
      ->leftJoin('htr.Transaction t')
      ->leftJoin('t.Tickets tck')
      
      ->leftJoin('h.Next n')
      ->leftJoin('n.HoldTransactions nht')
      
      ->orderBy('htr.rank DESC, htr.id')
    ;
    $hold = $q->fetchOne();
    if ( !$hold->next )
    {
      $this->getUser()->setFlash('error', __('You have nowhere to dump this waiting list.'));
      $this->redirect('hold_transaction/index');
    }
    
    $cpt = 0;
    foreach ( $hold->HoldTransactions as $ht )
    {
      if ( sfConfig::get('sf_web_debug', false) )
        error_log('Transaction: #'.$ht->transaction_id.' for Hold: '.$hold);
      
      if ( ($qty = $ht->pretickets - count(array_filter($ht->Transaction->Tickets->toKeyValueArray('id', 'seat_id')))) <= 0 )
        continue;
      
      $cpt++;
      $hold->Next->HoldTransactions[] = $ht;
      $ht->rank = min($hold->Next->HoldTransactions->toKeyValueArray('id', 'rank')) - 1000;
      $ht->save();
    }
    
    $this->getUser()->setFlash('notice', __('%%cpt%% transaction(s) have been dumped into this hold.', array('%%cpt%%' => $cpt)));
    $filters['hold_id'] = $hold->next;
    $this->setFilters($filters);
    $this->redirect('hold_transaction/index');
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
    
    $q = Doctrine::getTable('Hold')->createQuery('h', true)
      ->andWhere('h.id = ?', $filters['hold_id'])
      ->leftJoin('h.HoldTransactions htr')
      ->leftJoin('htr.Transaction t')
      ->leftJoin('t.Tickets tck')
      ->leftJoin('h.Seats s WITH s.id != tck.seat_id')
      ->orderBy('htr.rank, htr.id, s.rank, s.name')
    ;
    $hold = $q->fetchOne();
    foreach ( $hold->HoldTransactions as $ht )
    {
      if ( sfConfig::get('sf_web_debug', false) )
        error_log('Transaction: #'.$ht->transaction_id.' for Hold: '.$hold);
      
      $seater = new Seater(NULL, $hold);
      if ( ($qty = $ht->pretickets - count(array_filter($ht->Transaction->Tickets->toKeyValueArray('id', 'seat_id')))) <= 0 )
        continue;
      
      if ( sfConfig::get('sf_web_debug', false) )
        error_log('Transaction: #'.$ht->transaction_id.' for Hold: '.$hold);
      
      $seats = $seater->findSeats($qty);
      if ( $seats->count() == 0 )
      {
        for ( $i = 2 ; $i <= $qty ; $i++ )
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
      foreach ( $seats as $seat )
      {
        if ( sfConfig::get('sf_web_debug', false) )
          error_log('new ticket for seat: '.$seat);
        
        $ticket = new Ticket;
        $ticket->Seat = $seat;
        $ticket->manifestation_id = $hold->manifestation_id;
        $ticket->value = 0;
        $ticket->price_name = sfConfig::get('app_tickets_wip_price', 'WIP');
        $ht->Transaction->Tickets[] = $ticket;
      }
      $ht->Transaction->save();
    }
    
    $this->getUser()->setFlash('notice', __('The content of this hold has been seated, as much as it was possible.'));
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
    foreach ( $list = array('smaller_than' => 0, 'bigger_than' => 0, 'id' => 0) as $key => $value )
    if ( intval($request->getParameter($key)).'' === ''.$request->getParameter($key) )
      $list[$key] = intval($request->getParameter($key));
    else
      unset($list[$key]);
    $this->forward404Unless(isset($list['id']));
    
    $this->hold_transactions = Doctrine::getTable('HoldTransaction')->createQuery('ht')
      ->andWhere('ht.hold_id = (SELECT hht.hold_id FROM HoldTransaction hht WHERE hht.id = ?)', $list['id'])
      ->execute();
    
    // if no HoldTransaction exists for the given id
    $this->forward404Unless( count($list) > 1 && in_array($list['id'], $this->hold_transactions->getPrimaryKeys()) );
    
    $ranks = $this->hold_transactions->toKeyValueArray('id', 'rank');
    foreach ( $this->hold_transactions as $ht )
    if ( $ht->id == $list['id'] )
    {
      $this->hold_transaction = $ht;
      break;
    }
    
    if ( !isset($list['bigger_than']) )
      $ht->rank = floor($ranks[$list['smaller_than']]/2);
    elseif ( !isset($list['smaller_than']) )
      $ht->rank = $ranks[$list['bigger_than']]*2;
    else
      $ht->rank = floor($ranks[$list['bigger_than']] + ($ranks[$list['smaller_than']] - $ranks[$list['bigger_than']])/2);
    $ht->save();
  }
  
  protected function buildQuery()
  {
    $q = parent::buildQuery();
    $ht = $q->getRootAlias();
    $q->leftJoin("$ht.Transaction t")
      ->leftJoin("$ht.Hold h")
      ->leftJoin('t.Tickets tck WITH tck.duplicating IS NULL AND tck.cancelling IS NULL')
    ;
    return $q;
  }
}
