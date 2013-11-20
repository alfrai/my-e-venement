<?php

require_once dirname(__FILE__).'/../lib/invoiceGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/invoiceGeneratorHelper.class.php';

/**
 * invoice actions.
 *
 * @package    e-venement
 * @subpackage invoice
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class invoiceActions extends autoInvoiceActions
{
  public function executeShow(sfWebRequest $request)
  {
    $this->redirect('ticket/invoice?id='.$this->getRoute()->getObject()->transaction_id);
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->redirect('ticket/sell?id='.$this->getRoute()->getObject()->transaction_id);
  }
  public function executeNew(sfWebRequest $request)
  {
    $this->redirect('ticket/sell');
  }
  public function executeDelete(sfWebRequest $request)
  {
    $this->getUser()->setFlash('notice','To delete an invoice, please modify your transaction.');
    $this->redirect('ticket/sell?id='.$this->getRoute()->getObject()->transaction_id);
  }
  public function executeBatch(sfWebRequest $request)
  {
    $this->getUser()->setFlash('error','Sorry, you cannot do batch actions on invoices');
    $this->redirect('invoice/index');
  }
  public function executeBatchDelete(sfWebRequest $request)
  {
    $this->executeBatch($request);
  }
}
