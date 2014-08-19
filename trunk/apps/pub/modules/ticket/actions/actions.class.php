<?php

/**
 * ticket actions.
 *
 * @package    symfony
 * @subpackage ticket
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ticketActions extends sfActions
{
  public function executeAutoSeating(sfWebRequest $request)
  {
    $vel = sfConfig::get('app_tickets_vel');
    if ( !isset($vel['full_seating_by_customer']) ) $vel['full_seating_by_customer'] = false;
    
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->json = array(
      'error' => false,
      'success' => false,
    );
    
    if ( !$vel['full_seating_by_customer'] )
    {
      $this->json['error'] = array('message' => __('This plateform does not allow this action'));
      return 'Success';
    }
    
    $this->transaction = $this->getUser()->getTransaction();
    try {
      $this->recordTransaction($request);
    }
    catch ( liSeatedException $e )
    {
      $this->json['error'] = array('message' =>
        __($e->getMessage()).' '.
        __('We are sorry, you will have to choose your seats by yourself.')
      );
      return 'Success';
    }
    
    $seats = array();
    foreach ( $this->transaction->Tickets as $ticket )
      $seats[$ticket->gauge_id][$ticket->price_id][$ticket->seat_id] = (string)$ticket->Seat;
    
    $this->json['success'] = array(
      'message' => __('Congratulations, your tickets are now seated.'),
      'seats'   => $seats,
    );
    
    if ( sfConfig::get('sf_web_debug', false) && $request->hasParameter('debug') )
    {
      $this->getResponse()->setContentType('text/html');
      $this->getResponse()->sendHttpHeaders();
      $this->setLayout('public');
    }
  }
  
  protected function recordTransaction(sfWebRequest $request)
  {
    $prices = $request->getParameter('price');
    $cpt = 0;
    
    if (!( is_array($prices) && count($prices) != count($prices, COUNT_RECURSIVE) ))
      throw new liOnlineSaleException('The given data is incompatible with adding tickets into the cart.');
    
    // removing tickets-to-seat from the transaction before saving those that are seated correctly
    foreach ( $this->getUser()->getTransaction()->Tickets as $ticket )
    if ( $ticket->price_name && !$ticket->price_id )
      $ticket->delete();
    
    // adding prices in the Transaction
    foreach ( $prices as $gid => $gauge )
    foreach ( $gauge as $pid => $price )
    {
      $form = new PricesPublicForm($this->getUser()->getTransaction());
      $form->setPriceId($pid)->setGaugeId($gid);
      $price['transaction_id'] = $this->getUser()->getTransaction()->id;
      
      $form->bind($price);
      if ( $form->isValid() )
      {
        $time = microtime(true);
        $form->save();
        error_log('after form save '.(microtime(true)-$time));
        $cpt += $price['quantity'];
      }
      else
      {
        error_log('Adding prices in online sales: error for '.$form->getErrorSchema());
        throw new sfException($form->getErrorSchema());
      }
    }
    
    return $cpt;
  }
  
  public function executeCommit(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $cpt = $this->recordTransaction($request);
    
    $this->getUser()->setFlash('notice',__('%%nb%% ticket(s) have been added to your cart',array('%%nb%%' => $cpt)));
    $this->redirect('cart/show');
  }
}
