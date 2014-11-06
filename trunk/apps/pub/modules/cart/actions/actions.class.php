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
  
  public function preExecute()
  {
    $this->getUser()->addAuthException($this->getModuleName(), 'response');
    $this->dispatcher->notify(new sfEvent($this, 'pub.pre_execute', array('configuration' => $this->configuration)));
    parent::preExecute();
  }
  public function executeCommitSurvey(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    $params = $request->getParameter('survey', array());
    
    $s = new Survey;
    foreach ( $surveys = $this->getUser()->getTransaction()->getSurveysToFillIn() as $survey )
    if ( $survey->id == $params['id'] )
    {
      $s = $survey;
      break;
    }
    $this->form = new SurveyPublicForm($s);
    
    $this->form->bind($params);
    if ( !$this->form->isValid() )
      return 'Error';
    
    $this->form->save();
  }
  public function executeSurveys(sfWebRequest $request)
  {
    $this->forms = array();
    foreach ( $surveys = $this->getUser()->getTransaction()->getSurveysToFillIn() as $survey )
      $this->forms[] = new SurveyPublicForm($survey);
  }
  public function executeWidget(sfWebRequest $request)
  {
    try { $this->transac = $this->getUser()->getTransaction(); }
    catch ( liOnlineSaleException $e )
    { $this->transac = new Transaction; }
    
    if ( $this->transac === false )
      $this->transac = new Transaction;
    
    $this->timeout = false;
    
    // global timeout
    $time = strtotime(
      '+'.sfConfig::get('app_timeout_global', '1 hour'),
      strtotime($this->transac->created_at)
    ) - time();
    $this->global_timeout = $time <= 0 ? 'expired!' :
      floor($time/3600).':'.
      str_pad(floor($time%3600/60), 2, '0', STR_PAD_LEFT).':'.
      str_pad(floor($time%3600%60), 2, '0', STR_PAD_LEFT)
    ;
    if ( $time <= 0 )
      $this->timeout = true;
    
    // older item timeout
    $ticket = Doctrine::getTable('Ticket')->createQuery('tck')
      ->andWhere('tck.transaction_id = ?', $this->transac->id)
      ->orderBy('tck.updated_at')
      ->fetchOne();
    $this->older_item_timeout = false;
    if ( $ticket )
    {
      $time = strtotime(
        '+'.sfConfig::get('app_timeout_item', '40 minutes'),
        strtotime($ticket->updated_at)
      ) - time();
      $this->older_item_timeout = $time <= 0 ? 'expired!' :
        floor($time/3600).':'.
        str_pad(floor($time%3600/60), 2, '0', STR_PAD_LEFT).':'.
        str_pad(floor($time%3600%60), 2, '0', STR_PAD_LEFT)
      ;
      if ( $time <= 0 )
        $this->timeout = true;
    }
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
      throw new liEvenementException($e->message);
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('error',__('No cart to display'));
      $this->redirect('cart/show');
    }
    
    $this->getUser()->resetTransaction();
    $this->redirect('transaction/show?end=1&id='.$this->transaction->id);
  }
  public function executeCancel(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->getUser()->setFlash('error',__('You have just abandonned your payment, you can still empty / correct / validate your cart...'));
    $this->redirect('cart/show');
  }
  public function executeRegister(sfWebRequest $request)
  {
    // harden data
    $this->getContext()->getConfiguration()->hardenIntegrity();
    
    // already done first
    if ( sfConfig::get('app_contact_modify_coordinates_first', false) && $this->getUser()->getContact() )
      $this->redirect('cart/order');
    
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
    {
      $this->form->setDefault('phone_number', $contact->Phonenumbers[0]->number);
      $this->form->setDefault('phone_type',   $contact->Phonenumbers[0]->name);
      $this->form->removePassword();
    }
    
    $this->login = new LoginForm;
  }
  
  public function executeShow(sfWebRequest $request)
  {
    // harden data
    $this->getContext()->getConfiguration()->hardenIntegrity();
    
    // normal behavior
    $this->transaction_id = $this->getUser()->getTransaction()->id;
    
    $this->transaction = $this->getUser()->getTransaction();
    
    if ( $this->transaction->Tickets->count() == 0
      && $this->transaction->MemberCards->count() == 0
      && $this->transaction->BoughtProducts->count() == 0 )
    {
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('notice',__('Your cart is still empty, select tickets first...'));
      $this->redirect('event/index');
    }
    
    $this->redirect('transaction/show?id='.$this->transaction_id);
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
  
  protected function getMemberCardPaymentMethod()
  {
    return Doctrine::getTable('PaymentMethod')->createQuery('pm')
      ->andWhere('pm.member_card_linked = ?',true)
      ->andWhere('pm.display = ?',true)
      ->orderBy('id')
      ->fetchOne();
  }
  
  public static function sendConfirmationEmails(Transaction $transaction, sfAction $action)
  {
    return require(dirname(__FILE__).'/send-confirmation-emails.php');
  }
  
  protected function createPaymentsDoneByMemberCards(PaymentMethod $payment_method = NULL)
  {
    if ( is_null($payment_method) )
      $payment_method = $this->getMemberCardPaymentMethod();
    
    foreach ( $this->getUser()->getTransaction()->Tickets as $ticket )
    if ( $ticket->Price->member_card_linked )
    {
      $p_mc = new Payment;
      $p_mc->value = $ticket->value;
      $p_mc->Method = $payment_method;
      
      foreach ( $this->getUser()->getTransaction()->MemberCards as $mc )
      if ( $mc->hasPrice($ticket->price_id) && $mc->getValue() >= $ticket->value )
        $p_mc->member_card_id = $mc->id;
      
      if ( is_null($p_mc->member_card_id) )
      foreach ( $this->getUser()->getContact()->MemberCards as $mc )
      if ( $mc->transaction_id != $this->transaction->id && $mc->active
        && $mc->hasPrice($ticket->price_id) && $mc->getValue() >= $ticket->value )
        $p_mc->member_card_id = $mc->id;
      
      if ( !is_null($p_mc->member_card_id) )
        $this->getUser()->getTransaction()->Payments[] = $p_mc;
    }
  }
}
