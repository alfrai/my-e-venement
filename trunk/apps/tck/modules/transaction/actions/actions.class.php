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
    $this->form['professional_id'] = false;
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
    $ws['gauge_id'] = new sfWidgetFormInputHidden;
    $vs['gauge_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Gauge',
      'query' => Doctrine_Query::create()->from('Gauge g')
        ->leftJoin('g.Workspace w')
        ->leftJoin('w.Users wu')
        ->andWhere('wu.id = ?', $this->getUser()->getId()),
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
    $ws['value'] = new sfWidgetFormInput;
    $vs['value'] = new sfValidatorInteger(array('required' => false));
    $ws['created_at'] = new liWidgetFormJQueryDateText;
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
    
    require(dirname(__FILE__).'/complete.php');
    return '';
  }
  
  public function executeGetManifestations(sfWebRequest $request)
  {
    // initialization
    $this->getContext()->getConfiguration()->loadHelpers(array('CrossAppLink', 'Number'));
    $this->dealWithDebugMode($request);
    
    require(dirname(__FILE__).'/get-manifestations.php');
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
  
  protected function dealWithDebugMode(sfWebRequest $request)
  {
    $this->setTemplate('json');
    
    if ( $request->hasParameter('debug') && $this->getContext()->getConfiguration()->getEnvironment() == 'dev' )
    {
      $this->getResponse()->setContentType('text/html');
      sfConfig::set('sf_debug',true);
      $this->setLayout('layout');
    }
    else
    {
      sfConfig::set('sf_debug',false);
      sfConfig::set('sf_escaping_strategy', false);
    }
  }
}
