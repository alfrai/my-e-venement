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
  public function executeGetPassbook(sfWebRequest $request)
  {
    if ( !sfConfig::get('sf_web_debug', false) )
      return sfView::NONE;
    
    if ( !$request->hasParameter('debug') )
      sfConfig::set('sf_web_debug', false);
    
    $transaction = Doctrine::getTable('Transaction')->find(intval($request->getParameter('id')));
    
    $pass = EventTicket('111111', 'TEST TEST');
    $factory = new PassFactory('PASS-TYPE-IDENTIFIER', 'TEAM-IDENTIFIER', 'ORGANIZATION-NAME', '/path/to/p12/certificate', 'P12-PASSWORD', '/path/to/wwdr/certificate');
    $factory->setOutputPath(sfConfig::get('sf_upload_dir').'/passbook-'.date('YmdHis').'-'.$transaction->id);
    $factory->package($pass);
  }
  
  public function executeGetTickets(sfWebRequest $request)
  {
    if ( !sfConfig::get('sf_web_debug', false) )
      return sfView::NONE;
    
    if ( !$request->hasParameter('debug') )
      sfConfig::set('sf_web_debug', false);
    
    $transaction = Doctrine::getTable('Transaction')->find(intval($request->getParameter('id')));
    $this->tickets_html = $transaction->renderSimplifiedTickets();
    
    if ( !$request->hasParameter('pdf') )
      return 'Success';
    
    $pdf = new sfDomPDFPlugin();
    $pdf->setInput($content = $this->getPartial('get_tickets_pdf', $this->ticket_html));
    $this->getResponse()->setContentType('application/pdf');
    $this->getResponse()->setHttpHeader('Content-Disposition', 'attachment; filename="tickets.pdf"');
    return $this->renderText($pdf->execute());
  }
  
  public function executeTestSendEmail(sfWebRequest $request)
  {
    if ( !sfConfig::get('sf_web_debug', false) )
      return sfView::NONE;
    
    require_once(dirname(__FILE__).'/../../cart/actions/actions.class.php');
    if ( intval($request->getParameter('id')).'' !== $request->getParameter('id').'' )
      throw new liOnlineSaleException('Trying to access something without prerequisites.');
    
    $this->transaction = Doctrine::getTable('Transaction')->find(intval($request->getParameter('id')));
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
    if ( $this->transaction->contact_id !== $this->getUser()->getContact()->id )
    {
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('error', __('You cannot access an order which belongs to someone else'));
      $this->redirect('event/index');
    }
    
    $this->current_transaction = $this->transaction->id === $this->getUser()->getTransaction()->id;
    
    $q = Doctrine_Query::create()->from('Event e')
      ->leftJoin("e.Translation et WITH et.lang = '".$this->getUser()->getCulture()."'")
      ->leftJoin('e.Manifestations m')
      ->leftJoin('m.Gauges g')
      ->leftJoin('g.Workspace w')
      ->leftJoin('g.Tickets tck')
      ->leftJoin('tck.Price p')
      ->leftJoin('tck.Transaction t')
      ->leftJoin('t.Order o')
      ->andWhere('t.id = ?',$this->transaction->id)
      ->andWhere('t.type = ?','normal')
      ->andWhere('o.id IS NOT NULL OR tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL OR tck.transaction_id = ?', $this->transaction->id)
      ->andWhere('tck.cancelling IS NULL')
      ->andWhere('tck.id NOT IN (SELECT tck3.duplicating FROM Ticket tck3 WHERE tck3.duplicating IS NOT NULL)')
      ->andWhere('tck.id NOT IN (SELECT tck2.cancelling FROM Ticket tck2 WHERE tck2.cancelling IS NOT NULL)')
      ->andWhere('tck.id IS NOT NULL')
      ->orderBy('et.name, m.happens_at, w.name, g.id, p.id, tck.id');
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
    
    $this->form = new sfForm;
    $widgets = $this->form->getWidgetSchema();
    $widgets->setNameFormat('transaction[%s]');
    $widgets['description'] = new sfWidgetFormTextArea;
    
    }
    catch ( liOnlineSaleException $e )
    {
      $this->redirect('login/index');
    }
  }
  
  public function executeAddComment(sfWebRequest $request)
  {
    $transaction = Doctrine::getTable('Transaction')->findOneById($request->getParameter('id',0));
    $this->forward404Unless($transaction);
    
    $form = new sfForm;
    $validators = $form->getValidatorSchema();
    $validators['description'] = new sfValidatorString(array('required' => false));
    $values = $request->getParameter('transaction');
    $form->bind($values);
    
    if ( $form->isValid() )
    {
      $transaction->description = $values['description'];
      $transaction->save();
    }
    
    return sfView::NONE;
  }
  
  public function executeInvoice(sfWebRequest $request)
  {
    // faking the sfRouteObject
    $this->transaction = Doctrine::getTable('Transaction')->find($request->getParameter('id', 0));
    if ( $this->transaction->contact_id != $this->getUser()->getContact()->id )
      $this->forward404('No public transaction found for #'.$request->getParameter('id', 0));
    
    // forging some needed vars
    $printed = true;
    $manifestation_id = 0;
    $no_actions = true;
    
    // forging the request
    $request->setParameter('pdf', 'pdf');
    $request->setParameter('nocancel', 'true');
    
    // ugly hack... but working and easy
    require(dirname(__FILE__).'/accounting.php');
    return require(dirname(__FILE__).'/invoice.php');
  }
}
