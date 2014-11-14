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
  public function preExecute()
  {
    $this->dispatcher->notify(new sfEvent($this, 'pub.pre_execute', array('configuration' => $this->configuration)));
    parent::preExecute();
  }

  public function executeProducts(sfWebRequest $request)
  {
    $request->setParameter('target', 'products');
    $this->executeTickets($request);
  }
  public function executeTickets(sfWebRequest $request)
  {
    $transaction = Doctrine::getTable('Transaction')->find(intval($request->getParameter('id')));
    $this->setTemplate('tickets');
    
    // targets
    $targets = array(
      'tickets' => 'renderSimplifiedTickets',
      'products' => 'renderSimplifiedProducts',
    );
    if ( !isset($targets[$request->getParameter('type', 'tickets')]) )
      throw new liOnlineSaleException('Unknown target "'.$request->getParameter('type', 'tickets').'", please choose one: '.implode(', ', array_keys($targets)));
    $fct = $targets[$target = $request->getParameter('target', 'tickets')];
    if ( !method_exists($transaction, $fct) )
      throw new liOnlineSaleException('Bad configuration: '.get_class($transaction).' does not have any '.$fct.' method.');
    
    // debugging ?
    if ( sfConfig::get('sf_web_debug', false) && !$request->hasParameter('debug') )
      sfConfig::set('sf_web_debug', false);
    
    // ownership
    if ( $transaction->contact_id !== $this->getUser()->getContact()->id )
      throw new liOnlineSaleException('The delivery of tickets which belongs to someone else is not allowed');
    
    // content, and treatment
    $this->tickets_html = $transaction->$fct(
      array('barcode' => $request->getParameter('format') === 'html' ? 'html' : 'png')
    );
    switch ( $format = $request->getParameter('format', 'pdf') ) {
    case 'pdf':
      // content type
      $this->getResponse()->setContentType('application/pdf');
      if ( !sfConfig::get('sf_web_debug', false) )
      $this->getResponse()->setHttpHeader('Content-Disposition', 'attachment; filename="transaction-'.$transaction->id.'-'.$target.'.pdf"');

      $this->pdf = new sfDomPDFPlugin();
      $this->pdf->setInput($content = $this->getPartial('get_tickets_pdf', array('tickets_html' => $this->tickets_html)));
      echo $this->pdf->render();
      return sfView::NONE;
      //return $this->renderText($this->pdf->render()); // cannot do that for some particular cases that I do not understand... anyway...
    case 'html':
      $this->setLayout('nude');
      return 'Success';
    default:
      $this->dispatcher->notify($event = new sfEvent($this, 'pub.transaction_generate_other_format', array(
        'transaction' => $transaction,
        'target'      => $target,
        'format'      => $format,
        'headers'     => NULL,
        'content'     => NULL,
      )));
      foreach ( $event['headers'] as $key => $value )
        $this->getResponse()->setHttpHeader($key, $value);
      return $this->renderText($event['content']);
    }
  }
  
  public function executeSendEmail(sfWebRequest $request)
  {
    require_once(dirname(__FILE__).'/../../cart/actions/actions.class.php');
    if ( intval($request->getParameter('id')).'' !== $request->getParameter('id').'' )
      throw new liOnlineSaleException('Trying to access something without prerequisites.');
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    $this->transaction = Doctrine::getTable('Transaction')->find(intval($request->getParameter('id')));
    if ( !sfConfig::get('sf_web_debug', false)
      && $this->transaction->Order->count() == 0 || $this->transaction->contact_id != $this->getUser()->getTransaction()->id )
    {
      $this->getUser()->setFlash('error', __('The state of your order does not allow this action. Please contact us if necessary.'));
      $this->redirect('transaction/show?id='.$this->transaction->id);
    }
    
    cartActions::sendConfirmationEmails($this->transaction, $this);
    $this->getUser()->setFlash('success', __('Action successful'));
    $this->redirect('transaction/show?id='.$this->transaction->id);
  }
  
  public function executeShow(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->hardenIntegrity();
    
    try {
    
    $this->errors = array();
    
    if ( intval($request->getParameter('id')).'' !== $request->getParameter('id').'' )
      throw new liOnlineSaleException('Trying to access something without prerequisites.');
    
    $this->transaction = Doctrine::getTable('Transaction')->fetchOneById(intval($request->getParameter('id')));
    if ( $this->transaction->id != $this->getUser()->getTransactionId() && $this->transaction->contact_id !== $this->getUser()->getContact()->id )
    {
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('error', __('You cannot access an order which belongs to someone else'));
      $this->redirect('event/index');
    }
    
    $this->current_transaction = $this->transaction->id === $this->getUser()->getTransaction()->id;
    
    // Tickets
    $q = Doctrine_Query::create()->from('Event e')
      ->leftJoin('e.Translation et WITH et.lang = ?',$this->getUser()->getCulture())
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
      ->orderBy('m.happens_at, et.name, w.name, g.id, p.id, tck.id')
    ;
    if ( $this->getUser()->hasContact() )
      $q->andWhere('t.contact_id = ?',$this->getUser()->getContact()->id);
    else
      $q->andWhere('t.id = ?',$this->getUser()->getTransaction()->id);
    $this->events = $q->execute();
    
    // MemberCards
    $q = Doctrine::getTable('MemberCard')->createQuery('mc')
      ->leftJoin('mc.MemberCardType mct')
      ->andWhere('mc.transaction_id = ?', $this->transaction->id)
      ->orderBy('mc.expire_at, mct.name')
    ;
    if ( $this->getUser()->hasContact() )
      $q->andWhere('mc.contact_id = ?',$this->getUser()->getContact()->id);
    else
      $q->andWhere('mc.transaction_id = ?',$this->getUser()->getTransaction()->id);
    $this->member_cards = $q->execute();
    
    // Products
    $q = Doctrine::getTable('BoughtProduct')->createQuery('bp')
      ->leftJoin('bp.Transaction t')
      ->andWhere('t.id = ?', $this->transaction->id)
      ->leftJoin('bp.Declination d')
      ->leftJoin('d.Product p')
      ->leftJoin('p.Category c')
      ->leftJoin('c.Translation ct WITH ct.lang = ?', $this->getUser()->getCulture())
      ->orderBy('ct.name, bp.name, bp.declination, bp.value, bp.id')
    ;
    if ( $this->getUser()->hasContact() )
      $q->andWhere('t.contact_id = ?',$this->getUser()->getContact()->id);
    else
      $q->andWhere('t.id = ?',$this->getUser()->getTransaction()->id);
    $this->products = $q->execute();
    
    $this->end = $request->hasParameter('end');
    
    $this->form = new sfForm;
    $widgets = $this->form->getWidgetSchema();
    $widgets->setNameFormat('transaction[%s]');
    $widgets['description'] = new sfWidgetFormTextArea;
    $widgets['description']->setDefault($this->getUser()->getTransaction()->description);
    
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
    
    // forcing some needed vars
    $printed = true;
    $manifestation_id = 0;
    $no_actions = true;
    
    // forging the request
    if ( !$request->hasParameter('html') )
      $request->setParameter('pdf', 'pdf');
    $request->setParameter('nocancel', 'true');
    
    // ugly hack... but working and easy
    require(dirname(__FILE__).'/accounting.php');
    return require(dirname(__FILE__).'/invoice.php');
  }
}
