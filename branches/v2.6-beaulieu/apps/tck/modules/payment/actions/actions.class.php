<?php

require_once dirname(__FILE__).'/../lib/paymentGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/paymentGeneratorHelper.class.php';

/**
 * payment actions.
 *
 * @package    e-venement
 * @subpackage payment
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class paymentActions extends autoPaymentActions
{
  public function executeQuickDelete(sfWebRequest $request)
  {
    $payment = Doctrine::getTable('Payment')->findOneById($request->getParameter('id'));
    if ( $payment->transaction_id == $request->getParameter('transaction_id') )
      $payment->delete();
    $this->redirect('payment/index?transaction_id='.$request->getParameter('transaction_id'));
  }
  
  public function executeCreate(sfWebRequest $request)
  {
    try {
      parent::executeCreate($request);
    }
    catch ( liEvenementException $e )
    {
      $this->getUser()->setFlash('error',$e->getMessage().' - '.$this->payment->transaction_id);
      $this->setTemplate('new');
    }
    catch ( liMemberCardPaymentException $e )
    {
      $this->getUser()->setFlash('notice','Please specify the member card you want to impact through this payment');
      
      $this->form->setWidget('member_card_id',new sfWidgetFormDoctrineChoice(array(
        'query' => Doctrine::getTable('MemberCard')->createQuery('mc')->andWhereIn('mc.id',array_keys($this->payment->member_card_id))->andWhere('mc.expire_at > NOW()'),
        'model' => 'MemberCard',
        'order_by' => array('(SELECT sum(value) FROM Payment p WHERE mc.id = p.member_card_id) DESC, id',''),
      )));
      $this->form->setWidget('value',new sfWidgetFormInputHidden);
      $this->form->setWidget('payment_method_id',new sfWidgetFormInputHidden);
      
      $this->setTemplate('new');
    }
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    if (!( $tid = $request->getParameter('transaction_id',array()) ))
      return parent::executeIndex($request);
    if ( count($tid) == 1 && !$tid[0] ) // avoiding a common error on return request after creating a new payment
      return parent::executeIndex($request);
    
    if ( is_array($tid) )
    foreach ( $tid as $key => $id )
    if ( !$id )
      $tid[$key] = 0;
    
    if ( !is_array($tid) )
      $tid = array(intval($tid));
    
    parent::executeIndex($request);
    $this->pager = new sfDoctrinePager('Payment',1000);
    $this->pager->setQuery(
      Doctrine::getTable('Payment')->createQuery()
        ->andWhereIn('transaction_id',$tid)
        ->orderBy('updated_at DESC')
    );
    $this->pager->setPage(1);
    $this->pager->init();
    $this->sort = array('updated_at','DESC');
    $this->hasFilters = $this->getUser()->getAttribute('payment.filters', $this->configuration->getFilterDefaults(), 'admin_module');
  }
}
