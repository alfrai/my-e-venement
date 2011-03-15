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
    $this->form = new BaseForm();
  }
  
  // add contact
  public function executeAddContact(sfWebRequest $request)
  {
    $this->executeSell($request);
    
    if ( intval($request->getParameter('contact_id')) > 0 )
    {
      $this->transaction->contact_id = intval($request->getParameter('contact_id'));
      $this->transaction->save();
    }
    
    $this->setTemplate('sell');
  }
  // add manifestation
  public function executeAddManif(sfWebRequest $request)
  {
  }
  // remove tickets
  public function executeAddTicket(sfWebRequest $request)
  {
  }
  public function executeRemoveTicket(sfWebRequest $request)
  {
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
}
