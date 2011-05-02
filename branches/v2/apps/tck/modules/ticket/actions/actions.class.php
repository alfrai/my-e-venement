<?php

/**
 * ticket actions.
 *
 * @package    e-venement
 * @subpackage ticket
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ticketActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->redirect('ticket/sell');
  }
  
  public function executeSell(sfWebRequest $request)
  {
    if ( !($this->getRoute() instanceof sfObjectRoute) )
    {
      // this is a hack to be able to quickly commit things without problems
      if ( intval($request->getParameter('id')) > 0 )
        $this->transaction = Doctrine::getTable('Transaction')->findOneById(intval($request->getParameter('id')));
      else
      {
        $this->transaction = new Transaction();
        $this->transaction->save();
        $this->redirect('ticket/sell?id='.$this->transaction->id);
      }
    }
    else
    {
      $this->transaction = $this->getRoute()->getObject();
      if ( $this->transaction->closed )
      {
        $this->getUser()->setFlash('error','You have to re-open the transaction before to access it');
        return $this->redirect('ticket/respawn?id='.$this->transaction->id);
      }
    }
    
    $q = Doctrine::getTable('Price')->createQuery()
      ->orderBy('name');
    $this->prices = $q->execute();
    
    $payment = new Payment();
    $payment->transaction_id = $this->transaction->id;
    $this->payform = new PaymentForm($payment);
    
    $this->createTransactionForm(array('contact_id','professional_id'));
  }
  
  // add contact
  public function executeContact(sfWebRequest $request)
  {
    $values = $request->getParameter('transaction');
    
    $this->transaction = Doctrine::getTable('Transaction')->findOneById(
      $values['id']
    ? $values['id']
    : $request->getParameter('id')
    );
    
    if ( $request->hasParameter('delete-contact') )
    {
      $transaction = $request->getParameter('transaction');
      unset($transaction['contact_id']);
      unset($transaction['professional_id']);
      $request->setParameter('transaction',$transaction);
    }
    
    $this->createTransactionForm(
      array('contact_id','professional_id'),
      $request->getParameter('transaction', $request->getFiles('transaction'))
    );
    
    /*
    if ( $this->form->isValid() && $request->hasParameter('delete-contact') )
    {
      $this->transaction->professional_id = NULL;
      $this->transaction->contact_id = NULL;
      $this->transaction->save();
    }
    */
  }
  // add manifestation
  public function executeManifs(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('CrossAppLink');
    $values = $request->getParameter('transaction');
    
    $this->transaction = Doctrine::getTable('Transaction')->findOneById(
      $values['id']
    ? $values['id']
    : $request->getParameter('id')
    );
    
    $mids = array();
    foreach ( $this->transaction->Tickets as $ticket )
      $mids[] = $ticket->Manifestation->id;
    
    if ( $request->getParameter('manif_new') )
    {
      $eids = array();
      foreach ( Doctrine::getTable('Event')->search($request->getParameter('manif_new').'*') as $id )
        $eids[] = $id['id'];
      $q = Doctrine::getTable('Manifestation')->createQuery('m')
        ->andWhereIn('e.id',$eids)
        ->andWhereNotIn('m.id',$mids)
        ->orderBy('happens_at ASC');
      if ( !$this->getUser()->isSuperAdmin() )
        $q->andWhere('happens_at >= ?',date('Y-m-d'));
      
      $this->manifestations_add = $q->execute();
    }
    else
    {
      $eids = array();
      $q = Doctrine::getTable('Manifestation')
        ->createQuery()
        ->andWhereNotIn('m.id',$mids)
        ->orderBy('happens_at ASC')
        ->limit(10);
      //if ( !$this->getUser()->isSuperAdmin() )
        $q->andWhere('happens_at >= ?',date('Y-m-d'));
      $this->manifestations_add = $q->execute();
    }
  }
  
  // tickets public
  function executeTicket(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('CrossAppLink');
    $values = $request->getParameter('ticket');
    
    $tid = intval(
        $values['transaction_id']
      ? $values['transaction_id']
      : $request->getParameter('id')
    );
    
    if ( !$tid )
      $this->redirect('ticket/sell');
    
    unset($values['prices']);

    $ticket = new Ticket();
    $ticket->transaction_id = $tid;
    $this->form = new TicketForm($ticket);
    
    if ( $values )
    {
      $this->form->bind($values);
      
      if ( $this->form->isValid() )
      {
        $this->tickets = $this->form->save();
        if ( count($this->tickets) != intval($values['nb']) && intval($values['nb']) >= 0 )
        {
          $this->getUser()->setFlash('error',"This price doesn't exist for this manifestation !");
          $this->redirect('ticket/ticket?id='.$ticket->transaction_id);
        }
        $this->form->setWidget('contact_id', new sfWidgetFormInputHidden());
        $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $this->tickets)));
      }
    }
    
    $this->transaction = Doctrine::getTable('Transaction')->findOneById($tid);
    
    $q = Doctrine::getTable('Manifestation')->createQuery('m')
      ->leftJoin('m.Tickets tck')
      ->leftJoin('tck.Price tp')
      ->leftJoin('tck.Transaction t')
      ->andWhere('t.id = ?',$this->transaction->id)
      ->andWhere('tck.duplicate IS NULL')
      ->orderBy('e.name, tck.price_name');
    $this->manifestations = $q->execute();
    
    // ?? but necessary for ajax requests
    $this->setLayout('empty');
  }
  
  // validate the entire transaction
  public function executeValidate(sfWebRequest $request)
  {
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
  }
  public function executeClosed(sfWebRequest $request)
  {
    $this->transaction = $this->getRoute()->getObject();
    if ( !$this->transaction->closed )
    {
      $this->getUser()->setFlash('error', 'The transaction is not closed, verify and validate first');
      return $this->redirect('ticket/sell?id='.$this->transaction->id);
    }
  }
  
  public function executePrint(sfWebRequest $request)
  {
    if ( !($this->getRoute() instanceof sfObjectRoute) )
      return $this->redirect('ticket/sell');
    
    //$this->transaction = $this->getRoute()->getObject();
    $q = Doctrine::getTable('Transaction')
      ->createQuery('t')
      ->andWhere('t.id = ?',$request->getParameter('id'))
      ->andWhere('tck.duplicate IS NULL');
    $transactions = $q->execute();
    $this->transaction = $transactions[0];
    
    $this->duplicate = $request->getParameter('duplicate') == 'true';
    $this->tickets = array();
    foreach ( $this->transaction->Tickets as $ticket )
    if ( $request->getParameter('duplicate') == 'true' )
    {
      if ( strcasecmp($ticket->price_name,$request->getParameter('price_name')) == 0
        && $ticket->printed )
      {
        $newticket = $ticket->copy();
        $newticket->save();
        $ticket->duplicate = $newticket->id;
        $ticket->save();
        $this->tickets[] = $newticket;
      }
    }
    else
    {
      $this->duplicate = false;
      if ( !$ticket->printed )
      {
        $ticket->printed = true;
        $ticket->save();
        $this->tickets[] = $ticket;
      }
    }
    
    $this->setLayout('empty');
    
    if ( count($this->tickets) <= 0 )
      $this->setTemplate('close');
  }
  
  // remember / forget selected manifestations
  public function executeFlash(sfWebRequest $request)
  {
  }
  
  public function executeAccounting(sfWebRequest $request)
  {
    $this->transaction = $this->getRoute()->getObject();
    
    $this->totals = array('pet' => 0, 'tip' => 0, 'vat' => array('total' => 0));
    foreach ( $this->transaction->Tickets as $ticket )
    if ( !$ticket->duplicate )
    {
      $this->totals['tip'] += $ticket->value;
      
      if ( !isset($this->totals['vat'][$ticket->Manifestation->vat]) )
        $this->totals['vat'][$ticket->Manifestation->vat] = 0;
      $this->totals['vat'][$ticket->Manifestation->vat] += $ticket->value*$ticket->Manifestation->vat/100;
      $this->totals['vat']['total'] += $ticket->value*$ticket->Manifestation->vat/100;
    }
    
    $this->setLayout('empty');
  }
  // order
  public function executeOrder(sfWebRequest $request)
  {
    $this->executeAccounting($request);
    $this->order = $this->transaction->Order[0];
    
    if ( $request->hasParameter('cancel-order') )
    {
      $this->order->delete();
      return true;
    }
    else
    if ( is_null($this->order->id) )
      $this->order->save();
  }
  // invoice
  public function executeInvoice(sfWebRequest $request)
  {
    $this->executeAccounting($request);
    $this->invoice = $this->transaction->Invoice[0];
    if ( is_null($this->invoice->id) )
      $this->invoice->save();
    
  }
  
  public function executeRespawn(sfWebRequest $request)
  {
    $this->transaction_id = $request->getParameter('id');
  }
  
  public function executeAccess(sfWebRequest $request)
  {
    $id = intval($request->getParameter('id'));
    
    if ( $request->getParameter('reopen') )
    {
      $this->transaction = Doctrine::getTable('Transaction')
        ->findOneById($id);
      $this->transaction->closed = false;
      $this->transaction->save();
    }
    
    $this->redirect('ticket/sell?id='.$id);
  }
  
  public function executeGauge(sfWebRequest $request)
  {
    $workspace = $this->getUser()->getGuardUser()->Workspaces[0];
    $q = Doctrine::getTable('Gauge')->createQuery('g')
      ->andWhere('g.manifestation_id = ?', $mid = $request->getParameter('id'))
      ->andWhere('g.workspace_id = ?', $workspace->id); // to be performed
    $gauges = $q->execute();
    $this->gauge = $gauges[0];
    
    $q = Doctrine::getTable('Manifestation')->createQuery('m')
      ->addSelect('m.id')
      ->addSelect('sum(printed) AS sells')
      ->addSelect('sum(NOT printed AND t.transaction_id IN (SELECT o.transaction_id FROM order o)) AS orders')
      ->addSelect('sum(NOT printed AND t.transaction_id NOT IN (SELECT o2.transaction_id FROM order o2)) AS demands')
      ->andWhere('m.id = ?',$mid)
      ->leftJoin('m.Tickets t')
      ->andWhere('t.duplicate IS NULL')
      ->groupBy('m.id, e.name, me.name, m.happens_at, m.duration, p.name');
    $manifs = $q->execute();
    if ( $manifs->count() > 0 )
      $this->manifestation = $manifs[0];
    
    $this->height = array(
      'sells'   => $this->manifestation->sells / $this->gauge->value * 100,
      'orders'  => $this->manifestation->orders / $this->gauge->value * 100,
      'demands' => $this->manifestation->demands / $this->gauge->value * 100,
      'free'    => 100 - ($this->manifestation->sells+$this->manifestation->orders) / $this->gauge->value * 100
    );
    
    $this->setLayout('empty');
  }
  
  protected function createTransactionForm($excludes = array(), $parameters = null)
  {
    $this->form = new TransactionForm($this->transaction);
    
    // all fields to hide those wanted
    foreach ( $this->form->getWidgetSchema()->getFields() as $name => $widget )
    if ( !in_array($name,$excludes) )
    {
      $this->form->setWidget($name, new sfWidgetFormInputHidden());
    }
    
    // contact
    if ( $parameters )
    {
      $this->form->bind($parameters);
      if ( $this->form->isValid() )
      {
        $event = $this->form->save();
        if ( !is_null($this->transaction->contact_id) )
          $this->form->setWidget('contact_id', new sfWidgetFormInputHidden());
      }
    
      $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $event)));
    }
    
    // professional
    if ( !is_null($this->transaction->contact_id) && in_array('professional_id',$excludes) )
    {
      $query = Doctrine::getTable('Professional')->createQuery('p')
        ->andWhere('p.contact_id = ?',$this->transaction->contact_id);
      
      $proid = $this->form->getWidget('professional_id')
        ->setOption('query', $query);
      $this->form->getValidator('professional_id')
        ->setOption('query', $query);
    }
    
    return $this->form;
  }
}
