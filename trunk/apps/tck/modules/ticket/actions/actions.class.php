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
    require('sell.php');
  }
  
  public function executeBarcode(sfWebRequest $request)
  {
    require('barcode.php');
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
    require('cancel.php');
  }
  public function executeDuplicate(sfWebRequest $request)
  {
    require('duplicate.php');
  }
  public function executeContact(sfWebRequest $request)
  {
    require('contact.php');
  }
  
  // add manifestation
  public function executeManifs(sfWebRequest $request)
  {
    require('manifs.php');
  }
  
  // tickets public
  function executeTicket(sfWebRequest $request)
  {
    require('ticket.php');
  }
  
  // validate the entire transaction
  public function executeValidate(sfWebRequest $request)
  {
    require('validate.php');
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
    require('print.php');
  }
  public function executeIntegrate(sfWebRequest $request)
  {
    require('integrate.php');
  }
  public function executeRfid(sfWebRequest $request)
  {
    require('rfid.php');
  }
  
  // remember / forget selected manifestations
  public function executeFlash(sfWebRequest $request)
  {
  }
  
  public function executeAccounting(sfWebRequest $request, $printed = true, $manifestation_id = false)
  {
    require('accounting.php');
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
    $this->executeAccounting($request,true,$request->hasParameter('partial') ? $request->getParameter('manifestation_id') : false);
    
    $this->invoice = false;
    if ( $request->hasParameter('partial') && intval($request->getParameter('manifestation_id')) > 0 )
    {
      foreach ( $this->transaction->Invoice as $invoice )
      if ( $invoice->manifestation_id == intval($request->getParameter('manifestation_id')) )
        $this->invoice = $invoice;
      
      if ( !$this->invoice )
        $this->invoice = new Invoice();
      $this->transaction->Invoice[] = $this->invoice;
      $this->invoice->manifestation_id = intval($request->getParameter('manifestation_id'));
    }
    else
    {
      foreach ( $this->transaction->Invoice as $invoice )
      if ( is_null($invoice->manifestation_id) )
        $this->invoice = $invoice;
      
      if ( !$this->invoice )
        $this->invoice = new Invoice();
      $this->transaction->Invoice[] = $this->invoice;
    }
    
    $this->invoice->updated_at = date('Y-m-d H:i:s');
    $this->invoice->save();
  }
  
  public function executeRespawn(sfWebRequest $request)
  {
    $this->transaction_id = $request->hasParameter('id') ? intval($request->getParameter('id')) : '';
  }
  public function executeControl(sfWebRequest $request)
  {
    require('control.php');
  }
  public function executeBatchControl(sfWebRequest $request)
  {
    require('batch-control.php');
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
    require('gauge.php');
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
  
  public function executeContactPrices(sfWebRequest $request)
  {
    return require('contact-prices.php');
  }
  
  // partial printing
  public function executePartial(sfWebRequest $request)
  {
    require('partial.php');
  }
  
  // returns how many tickets exist for a contact on a metaevent for given price names
  protected function createTransactionForm($excludes = array(), $parameters = null)
  {
    // contact_id
    // manifesatation_id
    // tarif_names []
    require('transaction-form.php');
  }
}
