<?php

/**
 * cart actions.
 *
 * @package    symfony
 * @subpackage cart
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class cartActions extends sfActions
{
  protected $transaction = NULL;
  
  public function executeEmpty(sfWebRequest $request)
  {
    $this->getUser()->setAttribute('transaction_id',NULL);
    $this->redirect('cart/show');
  }
  public function executeDone(sfWebRequest $request)
  {
    try { $this->transaction = $this->getCurrentTransaction(); }
    catch ( liOnlineSaleException $e )
    {
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('error',__('No cart to display'));
      $this->redirect('cart/show');
    }
    
    $this->executeEmpty($request);
    $this->setTemplate('show');
  }
  public function executeCancel(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->getUser()->setFlash('error',__('You have just abandonned your payment, you can still empty / correct / validate your cart...'));
    $this->redirect('cart/show');
  }
  public function executeRegister(sfWebRequest $request)
  {
    $contact = $this->getUser()->getAttribute('contact_form_values',array());
    unset($contact['_csrf_token']);
    
    if ( !isset($this->form) )
      $this->form = new ContactPublicForm();
    $this->form->setDefaults($contact);
  }
  public function executeShow(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->errors = array();
    
    $q = Doctrine_Query::create()->from('Event e')
      ->leftJoin('e.Manifestations m')
      ->leftJoin('m.Gauges g')
      ->leftJoin('g.Workspace w')
      ->leftJoin('g.Tickets tck')
      ->leftJoin('tck.Price p')
      ->leftJoin('tck.Transaction t')
      ->andWhere('t.id = ?',$this->transaction instanceof Transaction ? $this->transaction_id : intval($this->getUser()->getAttribute('transaction_id')))
      ->andWhere('tck.id IS NOT NULL')
      ->andWhere('tck.sf_guard_user_id = ?',$this->getUser()->getId())
      ->andWhere('t.sf_guard_user_id = ?',$this->getUser()->getId())
      ->orderBy('e.name, m.happens_at, w.name, g.id, p.id, tck.id');
    $this->events = $q->execute();
      
    if ( $this->events->count() == 0 )
    {
      $this->getUser()->setFlash(__('Your cart is still empty, select tickets first...'));
      $this->redirect('event/index');
    }
    
  }
  
  public function executeOrder(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/order.php');
  }
  
  public function executeResponse(sfWebRequest $request)
  {
    // WHERE THE BANK PINGS BACK WHEN THE ORDER IS PAID
    return require(dirname(__FILE__).'/response.php');
  }
  
  protected function getCurrentTransaction()
  {
    if ( $this->transaction instanceof Transaction )
      return $this->transaction;
    
    if ( !$this->getUser()->getAttribute('transaction_id') )
      throw new liOnlineSaleException('No transaction_id available.');
    
    $this->transaction = Doctrine::getTable('Transaction')->createQuery('t')
      ->leftJoin('t.Contact c')
      ->andWhere('t.id = ?',$this->getUser()->getAttribute('transaction_id'))
      ->fetchOne();
    return $this->transaction;
  }
}
