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
  public function executeBarcode(sfWebRequest $request)
  {
    sfConfig::set('sf_web_debug', false);
    $this->getResponse()->setContentType('image/jpeg');
    $this->setLayout('no');
    
    $ticket = Doctrine::getTable('Ticket')->findOneById($request->getParameter('id'));
    $this->code = '';
    if ( is_object($ticket) )
    {
      $this->code = $ticket->getBarcode(sfConfig::get('app_seller_salt'));
      $ticket->barcode = $this->code;
      $ticket->save();
    }
  }
  
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
      if ( intval($request->getParameter('id')) > 0 )
        $this->redirect('ticket/sell?id='.intval($request->getParameter('id')));
      
      if ( intval($request->getParameter('id')) == 0 )
      {
        if ( $this->getUser()->hasFlash('error') )
          $this->getUser()->setFlash('error',$this->getUser()->getFlash('error'));
        if ( $this->getUser()->hasFlash('notice') )
          $this->getUser()->setFlash('notice',$this->getUser()->getFlash('error'));
        
        $this->transaction = new Transaction();
        $this->transaction->save();
        $this->redirect('ticket/sell?id='.$this->transaction->id);
      }
    }
    
    $this->transaction = $this->getRoute()->getObject();
    
    // if closed
    if ( $this->transaction->closed )
    {
      $this->getUser()->setFlash('error','You have to re-open the transaction before to access it');
      return $this->redirect('ticket/respawn?id='.$this->transaction->id);
    }
    
    // if not a "normal" transaction
    if ( $this->transaction->type != 'normal' )
    {
      $this->getUser()->setFlash('error',"You can respawn here only normal transactions");
      $this->redirect('ticket/sell');
    }
    
    $q = Doctrine::getTable('Price')->createQuery()
      ->orderBy('name');
    $this->prices = $q->execute();
    
    $payment = new Payment();
    $payment->transaction_id = $this->transaction->id;
    $this->payform = new PaymentForm($payment);
    
    $this->createTransactionForm(array('contact_id','professional_id'));
  }
  
  public function executeCancelBoot(sfWebRequest $request)
  {
    if ( intval($request->getParameter('id')) > 0 )
      $this->redirect('ticket/cancel?id='.intval($request->getParameter('id')));
    
    $this->pay = intval($request->getParameter('pay'));
    if ( $this->pay == 0 ) $this->pay = '';
    $this->setTemplate('cancelBoot');
  }
  public function executeCancel(sfWebRequest $request)
  {
    if ( intval($request->getParameter('ticket_id')) > 0 )
    {
      // get back the ticket to cancel
      $ticket = Doctrine::getTable('Ticket')
        ->findOneById(intval($request->getParameter('ticket_id')));
      if ( !$ticket )
      {
        $this->getUser()->setFlash('error',"Can't find the given ticket number in database...");
        $this->redirect('ticket/cancel');
      }
      
      // get back a potential existing transaction
      $transactions = Doctrine::getTable('Transaction')->createQuery('t')
        ->andWhere('t.transaction_id = ?',$ticket->transaction_id)
        ->execute();
      if ( $transactions->count() > 0 )
        $this->transaction = $transactions[0];
      else
      {
        // or create one
        $this->transaction = new Transaction();
        $this->transaction->type = 'cancellation';
        $this->transaction->transaction_id = $ticket->transaction_id;
      }
      
      // get back a potential cancellation ticket for this ticket_id
      $q = Doctrine::getTable('Ticket')->createQuery('t')
        ->andWhere('cancelling = ?',$ticket->id)
        ->orderBy('id DESC')
        ->limit(1);
      $duplicatas = $q->execute();
      
      // linking a new cancel ticket to this transaction
      $this->ticket = $ticket->copy();
      $this->ticket->cancelling = $ticket->id;
      $this->ticket->printed = false;
      $this->ticket->value = -$this->ticket->value;
      $this->transaction->Tickets[] = $this->ticket;
      $this->transaction->save();
      
      // saving the old ticket for duplication
      if ( $duplicatas->count() > 0 )
      {
        $duplicatas[0]->duplicate = $this->ticket->id;
        $duplicatas[0]->save();
      }
      
      // printing
      $this->getUser()->setFlash('notice','Ticket canceled.');
      $this->setTemplate('canceledTicket');
    }
    else
      $this->executeCancelBoot($request);
  }
  
  // add contact
  public function executeContact(sfWebRequest $request)
  {
    $values = $request->getParameter('transaction');
    $this->transaction = Doctrine::getTable('Transaction')
      ->findOneById($values['id'] ? $values['id'] : $request->getParameter('id'));
    
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
  }
  
  // add manifestation
  public function executeManifs(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('CrossAppLink');
    $values = $request->getParameter('transaction');
    $this->transaction = Doctrine::getTable('Transaction')
      ->findOneById($values['id'] ? $values['id'] : $request->getParameter('id'));
    
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
    
    $this->transaction = Doctrine::getTable('Transaction')
      ->findOneById($values['transaction_id'] ? $values['transaction_id'] : $request->getParameter('id'));
    
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
      ->andWhere('tck.duplicate IS NULL')
      ->leftJoin('m.Location l')
      ->leftJoin('m.Organizers o')
      ->leftJoin('m.Event e')
      ->leftJoin('e.MetaEvent me')
      ->leftJoin('e.Companies c');
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
    
    if ( count($this->tickets) <= 0 )
      $this->setTemplate('close');
    else
    {
      if ( !sfConfig::get('app_tickets_rfid') )
        $this->setLayout('empty');
      else
      {
        $this->form = new BaseForm();
        
        foreach ( $this->tickets as $ticket )
        {
          $w = new sfWidgetFormInputText();
          $w->setLabel($ticket->Manifestation.' '.$ticket->price_name);
          $this->form->setWidget('['.$ticket->id.'][othercode]',$w);
        }
        $this->form->getWidgetSchema()->setNameFormat('ticket%s');
        
        $this->setTemplate('rfid');
      }
    }
    
  }
  public function executeRfid(sfWebRequest $request)
  {
    $form = new BaseForm();
    $form->setValidator('othercode',new sfValidatorString(array('max_length' => 255, 'min_length' => 4)));
    
    foreach ( $request->getParameter('ticket') as $id => $ticket )
    {
      if ( intval($id) > 0 )
      {
        $ticket['_csrf_token'] = $request->getParameter('ticket_csrf_token');
        $form->bind($ticket);
        $t = Doctrine::getTable('Ticket')->findOneById($id);
        if ( $t )
        {
          $errors = $form->getGlobalErrors();
          foreach ( $errors as $key => $error )
            echo $key.' => '.$error;
          
          if ( $form->isValid() )
            $t->othercode = $ticket['othercode'];
          else
            $t->printed = false;
          $t->save();
        }
      }
      
      $this->setLayout('empty');
      $this->setTemplate('close');
    }
  }
  
  // remember / forget selected manifestations
  public function executeFlash(sfWebRequest $request)
  {
  }
  
  public function executeAccounting(sfWebRequest $request, $printed = true)
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
    
    $q = new Doctrine_Query();
    $q->from('Ticket t')
      ->leftJoin('t.Manifestation m')
      ->leftJoin('m.Event e')
      ->leftJoin('t.Price p')
      ->andWhere('t.transaction_id = ?',$this->transaction->id)
      ->andWhere('t.duplicate IS NULL')
      ->orderBy('m.happens_at, e.name, p.description, t.value');
    if ( $printed )
      $q->andWhere('t.printed');
    $this->tickets = $q->execute();
    
    $this->setLayout('empty');
  }
  // order
  public function executeOrder(sfWebRequest $request)
  {
    $this->executeAccounting($request,false);
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
    $this->invoice->updated_at = date('Y-m-d H:i:s');
    $this->invoice->save();
  }
  
  public function executeRespawn(sfWebRequest $request)
  {
    $this->transaction_id = $request->hasParameter('id') ? intval($request->getParameter('id')) : '';
  }
  public function executeControl(sfWebRequest $request)
  {
    $this->form = new ControlForm();
    $this->form->getWidget('checkpoint_id')->setOption('default', $this->getUser()->getAttribute('control.checkpoint_id'));
    $q = Doctrine::getTable('Checkpoint')->createQuery('c')
      ->andWhere('id = ?',$this->getUser()->getAttribute('control.checkpoint_id'));
    $this->form->getWidget('checkpoint_id')->setOption('query',$q);
    if ( count($request->getParameter($this->form->getName())) > 0 )
    {
      $this->form->bind($params = $request->getParameter($this->form->getName()),$request->getFiles($this->form->getName()));
      
      // filtering the checkpoints
      if ( $params['ticket_id'] )
      {
        $q->leftJoin('c.Event e')
          ->leftJoin('e.Manifestations m')
          ->leftJoin('m.Tickets t')
          ->where('t.'.sfConfig::get('app_tickets_id').' = ?',$params['ticket_id']);
      }
      
      if ( $this->form->isValid() )
      {
        $params = $request->getParameter($this->form->getName());
        
        $q = Doctrine::getTable('Control')->createQuery('c')
          ->leftJoin('c.Checkpoint c2')
          ->leftJoin('c2.Event e')
          ->leftJoin('e.Manifestations m')
          ->leftJoin('m.Tickets t')
          ->leftJoin('c.Ticket tc')
          ->andWhere('tc.'.sfConfig::get('app_tickets_id').' = ? AND c.checkpoint_id = ?',array($params['ticket_id'],$params['checkpoint_id']))
          ->andWhere('t.'.sfConfig::get('app_tickets_id').' = ?',$params['ticket_id'])
          ->orderBy('c.id DESC');
        $controls = $q->execute();
        
        $this->getUser()->setAttribute('control.checkpoint_id',$params['checkpoint_id']);
        
        if ( $controls->count() == 0 || !$controls[0]['Checkpoint']['legal'] )
        {
          $this->comment = $controls->count() > 0 ? $controls[0]['comment'] : '';
          
          $q = Doctrine::getTable('Checkpoint')->createQuery('c')
            ->leftJoin('c.Event e')
            ->leftJoin('e.Manifestations m')
            ->leftJoin('m.Tickets t')
            ->andWhere('t.'.sfConfig::get('app_tickets_id').' = ?',$params['ticket_id'])
            ->andWhere('c.id = ?',$params['checkpoint_id']);
          $checkpoint = $q->execute();
          
          $params = $request->getParameter($this->form->getName());
          if ( $checkpoint->count() > 0 )
          {
            if ( sfConfig::get('app_tickets_id') != 'id' )
            {
              $q = Doctrine::getTable('Ticket')->createQuery('t')
                ->andWhere(sfConfig::get('app_tickets_id').' = ?',$params['ticket_id'])
                ->andWhere('t.manifestation_id = (SELECT m.id FROM checkpoint c LEFT JOIN c.Event e LEFT JOIN e.Manifestations m WHERE c.id = ?)',$params['checkpoint_id']);
              $tickets = $q->execute();
              $params['ticket_id'] = $tickets[0]['id'];
              $this->form->bind($params);
            }
            if ( $this->form->isValid() )
              $this->form->save();
            $this->setTemplate('passed');
          }
          else
          {
            unset($params['checkpoint_id']);
            $this->form->bind($params);
          }
        }
        else
          $this->setTemplate('failed');
      }
    }
  }
  
  public function executeAccess(sfWebRequest $request)
  {
    $id = intval($request->getParameter('id'));
    
    if ( $request->getParameter('reopen') && $this->getUser()->hasCredential('tck-unblock') )
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
    
    $gauge = $this->gauge->value > 0 ? $this->gauge->value : 100;
    $this->height = array(
      'sells'   => $this->manifestation->sells / $gauge * 100,
      'orders'  => $this->manifestation->orders / $gauge * 100,
      'demands' => $this->manifestation->demands / $gauge * 100,
      'free'    => 100 - ($this->manifestation->sells+$this->manifestation->orders) / $gauge * 100
    );
    
    $this->setLayout('empty');
  }
  
  // single cash deal, eg: for cancellations
  public function executePay(sfWebRequest $request)
  {
    if ( !($this->getRoute() instanceof sfObjectRoute) )
    {
      if ( intval($request->getParameter('id')) > 0 )
        $this->redirect('ticket/pay?id='.intval($request->getParameter('id')));
      else
      {
        $this->getUser()->setFlash('error','You gave bad informations... try again.');
        $this->redirect('ticket/cancel');
      }
    }
    else
      $this->transaction = $this->getRoute()->getObject();
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
