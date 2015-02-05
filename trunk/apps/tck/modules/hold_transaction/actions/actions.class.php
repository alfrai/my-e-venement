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
    $this->json['quantity'] = $this->getQuantity();
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
    $this->json['quantity'] = $this->getQuantity();
  }
  protected function getQuantity(HoldTransaction $ht = NULL)
  {
    if ( is_null($ht) && isset($this->hold_transaction) )
      $ht = $this->hold_transaction;
    
    // the quantity of tickets + pretickets
    $cpt = 0;
    foreach ( $ht->Transaction->Tickets as $ticket )
    if ( !$ticket->cancelling && !$ticket->hasBeenCancelled() && !$ticket->duplicating )
      $cpt++;
    return $ht->pretickets + $cpt;
    
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
    
    parent::executeIndex($request);
  }
  
  public function executeEdit(sfWebRequest $request)
  {
    parent::executeEdit($request);
    $this->redirect('transaction/edit?id='.$this->hold_transaction->transaction_id);
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
