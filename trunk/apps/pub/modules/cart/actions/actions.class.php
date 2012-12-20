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
  
  public function executeWidget(sfWebRequest $request)
  {
    try { $this->transac = $this->getUser()->getTransaction(); }
    catch ( liOnlineSaleException $e )
    { $this->transac = new Transaction; }
    
    if ( $this->transac === false )
      $this->transac = new Transaction;
  }
  public function executeEmpty(sfWebRequest $request)
  {
    $this->getUser()->resetTransaction();
    $this->redirect('cart/show');
  }
  public function executeDone(sfWebRequest $request)
  {
    try { $this->transaction = $this->getUser()->getTransaction(); }
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
    $form_values = $this->getUser()->getAttribute('contact_form_values',array());
    unset($form_values['_csrf_token']);
    unset($form_values['id']);
    
    try { $contact = $this->getUser()->getContact() ? $this->getUser()->getContact() : new Contact; }
    catch ( liEvenementException $e )
    { $contact = new Contact; }
    
    if ( !isset($this->form) )
      $this->form = new ContactPublicForm($contact);
    
    if ( $contact->isNew() )
      $this->form->setDefaults($form_values);
    else
      $this->form->removePassword();
  }
  
  public function executeShow(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->errors = array();
    
    $this->transaction_id = $this->getUser()->getTransaction()->id;
    
    $q = Doctrine_Query::create()->from('Event e')
      ->leftJoin('e.Manifestations m')
      ->leftJoin('m.Gauges g')
      ->leftJoin('g.Workspace w')
      ->leftJoin('g.Tickets tck')
      ->leftJoin('tck.Price p')
      ->leftJoin('tck.Transaction t')
      ->andWhere('t.id = ?',$this->transaction_id)
      ->andWhere('tck.id IS NOT NULL')
      ->andWhere('tck.sf_guard_user_id = ?',$this->getUser()->getId())
      ->andWhere('t.sf_guard_user_id = ?',$this->getUser()->getId())
      ->orderBy('e.name, m.happens_at, w.name, g.id, p.id, tck.id');
    $this->events = $q->execute();
    
    $this->member_cards = Doctrine::getTable('MemberCard')->createQuery('mc')
      ->leftJoin('mc.MemberCardType mct')
      ->andWhere('mc.transaction_id = ?', $this->transaction_id)
      ->orderBy('mc.expire_at, mct.name')
      ->execute();
    
    if ( $this->events->count() == 0 && $this->member_cards->count() == 0 )
    {
      $this->getUser()->setFlash('notice',__('Your cart is still empty, select tickets first...'));
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
}
