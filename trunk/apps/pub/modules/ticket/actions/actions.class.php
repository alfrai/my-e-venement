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
  public function executeGetOrphans(sfWebRequest $request)
  {
    return require(dirname(__FILE__).'/get-orphans.php');
  }
  public function executeRemoveTicket(sfWebRequest $request)
  {
    return require(dirname(__FILE__).'/remove-ticket.php');
  }
  public function executeAddSeat(sfWebRequest $request)
  {
    return require(dirname(__FILE__).'/add-seat.php');
  }
  public function executeAutoSeating(sfWebRequest $request)
  {
    return require(dirname(__FILE__).'/auto-seating.php');
  }
  public function executeModSeatedTickets(sfWebRequest $request)
  {
    return require(dirname(__FILE__).'/mod-seated-tickets.php');
  }
  
  protected function jsonError($messages = array(), sfWebRequest $request)
  {
    if ( !is_array($messages) )
      $messages = array($messages);
    $this->json['error']['message'] = $messages;
    
    error_log('app: pub, module: ticket --> '.implode(' | ', $messages));
    $this->debug($request);
    return 'Error';
  }
  protected function debug(sfWebRequest $request, $no_get_param = false)
  {
    if ( sfConfig::get('sf_web_debug', false) && ($no_get_param || $request->hasParameter('debug')) )
    {
      $this->debug = true;
      $this->getResponse()->setContentType('text/html');
      $this->getResponse()->sendHttpHeaders();
      $this->setLayout('public');
    }
    else
      sfConfig::set('sf_web_debug', false);
  }
  
  protected function recordTransaction(sfWebRequest $request)
  {
    $prices = $request->getParameter('price', array());
    if ( !is_array($prices) )
      $prices = array();
    $cpt = 0;
    
    if (!( is_array($prices) && count($prices) != count($prices, COUNT_RECURSIVE) ))
      throw new liOnlineSaleException('The given data is incompatible with adding tickets into the cart.');
    
    // adding prices in the Transaction
    foreach ( $prices as $gid => $gauge )
    foreach ( $gauge as $pid => $price )
    if ( $pid )
    {
      if (!( isset($price['seat_id']) && $price['seat_id'] && is_array($price['seat_id']) ))
        $price['seat_id'] = array();
      
      $this->form = new PricesPublicForm($this->getUser()->getTransaction());
      $this->form->setPriceId($pid)->setGaugeId($gid);
      $price['transaction_id'] = $this->getUser()->getTransaction()->id;
      
      // remember the seat_id of tickets that are being removed before adding new ones matching the form
      foreach ( $this->getUser()->getTransaction()->Tickets as $ticket )
      if ( $ticket->gauge_id == $gid && ($ticket->price_id == $pid || $ticket->price_name && !$ticket->price_id) && !in_array($ticket->seat_id, $price['seat_id']) )
        $price['seat_id'][] = $ticket->seat_id;
      
      $this->form->bind($price);
      if ( $this->form->isValid() )
      {
        $this->form->save();
        $cpt += $price['quantity'];
      }
      else
      {
        error_log('Adding prices in online sales: error for '.$this->form->getErrorSchema().' with '.print_r($price,true));
        throw new sfException($this->form->getErrorSchema());
      }
    }
    
    return $cpt;
  }
  
  public function executeCommit(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $cpt = $this->recordTransaction($request, true);
    
    $this->getUser()->setFlash('notice',__('%%nb%% ticket(s) have been added to your cart',array('%%nb%%' => $cpt)));
    $this->redirect('cart/show');
  }
}
