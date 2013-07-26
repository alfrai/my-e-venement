<?php

/**
 * transaction actions.
 *
 * @package    symfony
 * @subpackage transaction
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class transactionActions extends sfActions
{
  public function executeTestSendEmail(sfWebRequest $request)
  {
    require_once(dirname(__FILE__).'/../../cart/actions/actions.class.php');
    if ( intval($request->getParameter('id')).'' !== $request->getParameter('id').'' )
      throw new liOnlineSaleException('Trying to access something without prerequisites.');
    
    $this->transaction = Doctrine::getTable('Transaction')->fetchOneById(intval($request->getParameter('id')));
    cartActions::sendConfirmationEmails($this->transaction);
    return sfView::NONE;
  }
  
  public function executeShow(sfWebRequest $request)
  {
    try {
    
    $this->errors = array();
    
    if ( intval($request->getParameter('id')).'' !== $request->getParameter('id').'' )
      throw new liOnlineSaleException('Trying to access something without prerequisites.');
    
    $this->transaction = Doctrine::getTable('Transaction')->fetchOneById(intval($request->getParameter('id')));
    $this->current_transaction = $this->transaction->id === $this->getUser()->getTransaction()->id;
    
    $q = Doctrine_Query::create()->from('Event e')
      ->leftJoin('e.Manifestations m')
      ->leftJoin('m.Gauges g')
      ->leftJoin('g.Workspace w')
      ->leftJoin('g.Tickets tck')
      ->leftJoin('tck.Price p')
      ->leftJoin('tck.Transaction t')
      ->leftJoin('t.Order o')
      ->andWhere('t.id = ?',$this->transaction->id)
      ->andWhere('t.type = ?','normal')
      ->andWhere('o.id IS NOT NULL OR tck.printed = ? OR tck.integrated = ? OR tck.transaction_id = ?',array(true,true,$this->transaction->id))
      ->andWhere('tck.cancelling IS NULL')
      ->andWhere('tck.id NOT IN (SELECT tck3.duplicating FROM Ticket tck3 WHERE tck3.duplicating IS NOT NULL)')
      ->andWhere('tck.id NOT IN (SELECT tck2.cancelling FROM Ticket tck2 WHERE tck2.cancelling IS NOT NULL)')
      ->andWhere('tck.id IS NOT NULL')
      ->orderBy('e.name, m.happens_at, w.name, g.id, p.id, tck.id');
    
    if ( $this->getUser()->hasContact() )
      $q->andWhere('t.contact_id = ?',$this->getUser()->getContact()->id);
    else
      $q->andWhere('t.id = ?',$this->getUser()->getTransaction()->id);
    
    $this->events = $q->execute();

    $q = Doctrine::getTable('MemberCard')->createQuery('mc')
      ->leftJoin('mc.MemberCardType mct')
      ->andWhere('mc.transaction_id = ?', $this->transaction->id)
      ->orderBy('mc.expire_at, mct.name');
    
    if ( $this->getUser()->hasContact() )
      $q->andWhere('mc.contact_id = ?',$this->getUser()->getContact()->id);
    else
      $q->andWhere('mc.transaction_id = ?',$this->getUser()->getTransaction()->id);
    
    $this->member_cards = $q->execute();
    
    $this->end = $request->hasParameter('end');
    
    }
    catch ( liOnlineSaleException $e )
    {
      $this->redirect('login/index');
    }
  }
}
