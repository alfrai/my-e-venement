<?php

require_once dirname(__FILE__).'/../lib/transactionGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/transactionGeneratorHelper.class.php';

/**
 * transaction actions.
 *
 * @package    e-venement
 * @subpackage transaction
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class transactionActions extends autoTransactionActions
{
  public function executeRegistered(sfWebRequest $request)
  {
    $this->transaction = $this->getRoute()->getObject();
    $this->forms = array();
    
    foreach ( $this->transaction->Tickets as $ticket )
    if ( $ticket->gauge_id == $request->getParameter('gauge_id', 0)
      && $ticket->price_id == $request->getParameter('price_id', 0)
      && !$ticket->duplicating && !$ticket->cancelling )
    {
      $form = new TicketRegisteredForm($ticket);
      $this->forms[] = $form;
    }
  }
  public function executeRegister(sfWebRequest $request)
  {
    $data = $request->getParameter('ticket');
    $this->form = new TicketRegisteredForm;
    $this->form->bind($data);
    
    if ( $request->hasParameter('debug') && sfConfig::get('sf_web_debug') )
    {
      $this->debug = true;
      $this->getResponse()->setContentType('text/html');
      $this->getResponse()->sendHttpHeaders();
      $this->setLayout('layout');
    }
    
    if ( !$this->form->isValid() )
      return 'Error';
    
    $this->getContext()->getConfiguration()->loadHelpers(array('Url', 'I18N'));
    
    $ticket = $this->form->save();
    $this->json = array('success' => array(
      'message'   => __('Ticket #%%id%% registered', array('%%id%%' => $ticket->id)),
      'url_back' => url_for('transaction/registered'
        .'?id='.$ticket->transaction_id
        .'&price_id='.$ticket->price_id
        .'&gauge_id='.$ticket->gauge_id
      ),
    ));
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers(array('CrossAppLink','I18N'));
    parent::executeEdit($request);
    
    if ( $this->transaction->closed )
    {
      $this->getUser()->setFlash('error', __('You have to re-open the transaction before accessing it'));
      $this->redirect('transaction/respawn?id='.$this->transaction->id);
    }
    if ( $this->transaction->type == 'cancellation' )
    {
      $this->getUser()->setFlash('error',__("You can respawn here only normal transactions"));
      $this->redirect('ticket/pay?id='.$this->transaction->id);
    }
    
    $this->form = array();
    
    // Contact
    $this->form['contact_id'] = new sfForm;
    $this->form['contact_id']->setDefault('contact_id', $this->transaction->contact_id);
    $ws = $this->form['contact_id']->getWidgetSchema()->setNameFormat('transaction[%s]');
    $vs = $this->form['contact_id']->getValidatorSchema();
    $ws['contact_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Contact',
      'url'   => cross_app_url_for('rp', 'contact/ajax'),
    ));
    $vs['contact_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Contact',
      'required' => false,
    ));
    
    // Professional
    $this->form['professional_id'] = new sfForm;
    $this->form['professional_id']->setDefault('professional_id', $this->transaction->professional_id);
    $ws = $this->form['professional_id']->getWidgetSchema()->setNameFormat('transaction[%s]');
    $vs = $this->form['professional_id']->getValidatorSchema();
    $ws['professional_id'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'Professional',
      'add_empty' => true,
      'query' => Doctrine::getTable('Professional')->createQuery('p')->andWhere('c.id = ?',$this->transaction->contact_id),
      'method' => 'getFullDesc',
    ));
    $vs['professional_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Professional',
      'required' => false,
    ));
    
    // Deposit (calling the "more" template)
    $this->form['more'] = new sfForm;
    $this->form['more']->setDefault('deposit', $this->transaction->deposit);
    $ws = $this->form['more']->getWidgetSchema()->setNameFormat('transaction[%s]');
    $vs = $this->form['more']->getValidatorSchema();
    $ws['deposit'] = new sfWidgetFormInputCheckbox(array(
    ));
    $vs['deposit'] = new sfValidatorBoolean(array(
      'required' => false,
    ));
    
    // DESCRIPTION
    $this->form['description'] = new sfForm;
    $this->form['description']->setDefault('description', $this->transaction->description);
    $ws = $this->form['description']->getWidgetSchema()->setNameFormat('transaction[%s]');
    $vs = $this->form['description']->getValidatorSchema();
    $ws['description'] = new sfWidgetFormTextarea();
    $vs['description'] = new sfValidatorString(array('required' => false,));
    
    // PRICES
    $this->form['price_new'] = new sfForm;
    $ws = $this->form['price_new']->getWidgetSchema()->setNameFormat('transaction[price_new][%s]');
    $vs = $this->form['price_new']->getValidatorSchema();
    $ws['qty'] = new sfWidgetFormInput;
    $vs['qty'] = new sfValidatorInteger(array(
      'max' => 251,
      'required' => false, // if no qty is set, then "1" is used
    ));
    $ws['price_id'] = new sfWidgetFormInputHidden;
    $vs['price_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Price',
      // already includes in PriceTable the control of user's credentials
    ));
    $ws['declination_id'] = new sfWidgetFormInputHidden;
    $ws['type'] = new sfWidgetFormInputHidden;
    $vs['type'] = new sfValidatorChoice(array(
      'choices' => array('declination', 'gauge'),
    ));
    $ws['id'] = new sfWidgetFormInputHidden;
    $vs['id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Transaction',
      'query' => Doctrine::getTable('Transaction')->createQuery('t')
        ->andWhere('t.closed = ?', false)
        ->andWhere('t.id = ?', $this->transaction->id),
    ));
    $this->form['price_new']->setDefault('id', $this->transaction->id);
    $ws['state'] = new sfWidgetFormInputHidden;
    $vs['state'] = new sfValidatorChoice(array(
      'choices' => array('', 'integrated'),
      'required' => false,
    ));
    
    // NEW "PRODUCTS"
    $this->form['content'] = array();
    $this->form['content']['manifestations'] = new sfForm;
    $ws = $this->form['content']['manifestations']->getWidgetSchema();
    $vs = $this->form['content']['manifestations']->getValidatorSchema();
    $vs['manifestation_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
      'query' => Doctrine::getTable('Manifestation')->createQuery('m')->select('m.id')
        ->andWhere('m.reservation_confirmed = ? AND m.blocking = ?',array(true,true))
        ->andWhere('pu.sf_guard_user_id = ?', $this->getUser()->getId()),
    ));
    $this->form['content']['store'] = new sfForm;
    $ws = $this->form['content']['store']->getWidgetSchema();
    $vs = $this->form['content']['store']->getValidatorSchema();
    $vs['product_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
      'query' => Doctrine::getTable('Product')->createQuery('pdt')->select('pdt.id')
        ->andWhereIn('pdt.meta_event_id IS NULL OR pdt.meta_event_id', array_keys($this->getUser()->getMetaEventsCredentials()))
        ->leftJoin('pdt.Prices p')
        ->leftJoin('p.Users pu')
        ->andWhere('pu.sf_guard_user_id = ?', $this->getUser()->getId()),
    ));
    $this->form['content']['store']->integrate = new sfForm;
    $ws = $this->form['content']['store']->integrate->getWidgetSchema()->setNameFormat('transaction[store_integrate][%s]');
    $vs = $this->form['content']['store']->integrate->getValidatorSchema();
    $ws['id'] = new sfWidgetFormInputHidden;
    $vs['id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Transaction',
      'query' => Doctrine::getTable('Transaction')->createQuery('t')
        ->andWhere('t.closed = ?', false)
        ->andWhere('t.id = ?', $this->transaction->id),
    ));
    $this->form['content']['store']->integrate->setDefault('id', $this->transaction->id);

    // NEW PAYMENT
    $this->form['payment_new'] = new sfForm;
    $ws = $this->form['payment_new']->getWidgetSchema()->setNameFormat('transaction[payment_new][%s]');
    $vs = $this->form['payment_new']->getValidatorSchema();
    $ws['payment_method_id'] = new sfWidgetFormDoctrineChoice(array(
      'expanded' => true,
      'model' => 'PaymentMethod',
      'order_by' => array('name', ''),
      'query' => $q = Doctrine::getTable('PaymentMethod')->createQuery('pm')
        ->andWhere('pm.display = ?',true),
    ));
    $vs['payment_method_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'PaymentMethod',
      'query' => $q,
    ));
    $vs['member_card_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'MemberCard',
      'query' => Doctrine::getTable('MemberCard')->createQuery('mc')
        ->andWhere('mc.expire_at > NOW()')
        ->andWhere('mc.contact_id = ?', $this->transaction->contact_id),
      'required' => false,
    ));
    $ws['value'] = new sfWidgetFormInput;
    $vs['value'] = new sfValidatorNumber(array('required' => false));
    $ws['created_at'] = new liWidgetFormJQueryDateText(array('culture' => $this->getUser()->getCulture()));
    $vs['created_at'] = new sfValidatorDate(array('required' => false));
    
    // DELETE PAYMENT
    $this->form['payments_list'] = new sfForm;
    $ws = $this->form['payments_list']->getWidgetSchema()->setNameFormat('transaction[payments_list][%s]');
    $vs = $this->form['payments_list']->getValidatorSchema();
    $ws['id'] = new sfWidgetFormInputHidden;
    $vs['id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Payment',
      'query' => Doctrine::getTable('Payment')->createQuery('p')
        ->leftJoin('p.Transaction t')
        ->andWhere('t.closed = ?', false),
    ));
    
    // CLOSE THE TRANSACTION
    $this->form['close'] = new sfForm;
    $ws = $this->form['close']->getWidgetSchema()->setNameFormat('transaction[close][%s]');
    $vs = $this->form['close']->getValidatorSchema();
    $ws['id'] = new sfWidgetFormInputHidden;
    $vs['id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Transaction',
      'query' => Doctrine::getTable('Transaction')->createQuery('t')
        ->andWhere('t.closed = ?', false)
        ->andWhere('t.id = ?', $this->transaction->id),
    ));
    $this->form['close']->setDefault('id', $this->transaction->id);
  }
  
  public function executeRespawn(sfWebRequest $request)
  {
    $this->sf_request = $request;
  }
  public function executeAccess(sfWebRequest $request)
  {
    $transaction = Doctrine::getTable('Transaction')->findOneById($request->getParameter('id'));
    
    // reopen the transaction
    if ( $transaction->closed
      && $request->hasParameter('reopen')
      && $this->getUser()->hasCredential('tck-unblock')
    )
    {
      $transaction->closed = false;
      $transaction->save();
    }
    
    $this->redirect('transaction/edit?id='.$request->getParameter('id'));
  }
  
  public function executeComplete(sfWebRequest $request)
  {
    // initialization
    $this->executeEdit($request);
    $this->dealWithDebugMode($request);
    $vs['declination_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Gauge',
      'query' => Doctrine_Query::create()->from('Gauge g')
        ->leftJoin('g.Workspace w')
        ->leftJoin('w.Users wu')
        ->andWhere('wu.id = ?', $this->getUser()->getId()),
    ));
    
    require(dirname(__FILE__).'/complete.php');
    return '';
  }
  
  public function executeGetManifestations(sfWebRequest $request)
  { return $this->getAbstract($request, 'manifestations'); }
  
  public function executeGetStore(sfWebRequest $request)
  { return $this->getAbstract($request, 'store'); }
  protected function getAbstract(sfWebRequest $request, $type)
  {
    // initialization
    $this->getContext()->getConfiguration()->loadHelpers(array('CrossAppLink', 'Number'));
    $this->dealWithDebugMode($request);
    
    require(dirname(__FILE__).'/get-abstract.php');
    return '';
  }
  
  public function executeGetPayments(sfWebRequest $request)
  {
    // initialization
    $this->getContext()->getConfiguration()->loadHelpers(array('CrossAppLink', 'Number'));
    $this->dealWithDebugMode($request);
    
    require(dirname(__FILE__).'/get-payments.php');
    return '';
  }
  
  public function executeNew(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers(array('I18N'));
    parent::executeNew($request);
    
    $this->transaction->save();
    
    $this->getUser()->setFlash('success', __('Transaction created'));
    $this->redirect('transaction/edit?id='.$this->transaction->id);
  }
  public function executeShow(sfWebRequest $request)
  { $this->redirect('transaction/edit?id='.$request->getParameter('id')); }
  public function executeBatchDelete(sfWebRequest $request)
  { $this->forward404('You are not supposed to be here...'); }
  public function executeDelete(sfWebRequest $request)
  { $this->forward404('You are not supposed to be here...'); }
  public function executeCreate(sfWebRequest $request)
  { $this->forward404('You are not supposed to be here...'); }
  public function executeUpdate(sfWebRequest $request)
  { $this->forward404('You are not supposed to be here...'); }
  
  public function executeSeatsFirst(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers(array('Url'));
    $this->getUser()->setFlash('referer', url_for('transaction/closeWindow'));
    $this->redirect('ticket/seatsAllocation?id='.$request->getParameter('id').'&gauge_id='.$request->getParameter('gauge_id').'&add_tickets=true');
  }
  public function executeCloseWindow(sfWebRequest $request)
  { }
  
  protected function dealWithDebugMode(sfWebRequest $request)
  {
    $this->setTemplate('json');
    
    if ( $request->hasParameter('debug') && sfConfig::get('sf_web_debug', false) )
    {
      $this->getResponse()->setContentType('text/html');
      $this->setLayout('layout');
    }
    else
    {
      sfConfig::set('sf_debug',false);
      sfConfig::set('sf_escaping_strategy', false);
    }
  }
}
