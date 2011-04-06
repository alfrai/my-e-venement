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
      $this->transaction = $this->getRoute()->getObject();
    
    $q = Doctrine::getTable('Price')->createQuery()
      ->orderBy('name');
    $this->prices = $q->execute();
    
    $this->createTransactionForm(array('contact_id','professional_id'));
  }
  
  // add contact
  public function executeAddContact(sfWebRequest $request)
  {
    $values = $request->getParameter('transaction');
    
    $this->transaction = Doctrine::getTable('Transaction')->findOneById(
      $values['id']
    ? $values['id']
    : $request->getParameter('id')
    );
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
    
    $this->transaction = Doctrine::getTable('Transaction')->findOneById(
      $values['id']
    ? $values['id']
    : $request->getParameter('id')
    );
    
    if ( $request->getParameter('manif_new') )
    {
      $eids = array();
      foreach ( Doctrine::getTable('Event')->search($request->getParameter('manif_new').'*') as $id )
        $eids[] = $id['id'];
      $this->manifestations_add = Doctrine::getTable('Manifestation')->createQuery()
        ->andWhereIn('e.id',$eids)
        ->execute();
    }
    else
      $this->manifestations_add = array();
  }
  
  // tickets
  public function executeTicket(sfWebRequest $request)
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
      ->orderBy('e.name, tck.price_name');
    $this->manifestations = $q->execute();
    
    // ?? but necessary for ajax requests
    $this->setLayout('empty');
  }
  
  // add payment
  public function executeAddPayment(sfWebRequest $request)
  {
  }
  // remove payement
  public function executeRemovePayment(sfWebRequest $request)
  {
  }
  // validate the entire transaction
  public function executeConfirmation(sfWebRequest $request)
  {
  }
  
  // remember / forget selected manifestations
  public function executeFlash(sfWebRequest $request)
  {
  }
  
  // order
  public function executeGetOrder(sfWebRequest $request)
  {
  }
  // invoice
  public function executeGetInvoice(sfWebRequest $request)
  {
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
