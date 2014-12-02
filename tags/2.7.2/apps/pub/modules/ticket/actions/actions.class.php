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
  public function preExecute()
  {
    $this->dispatcher->notify(new sfEvent($this, 'pub.pre_execute', array('configuration' => $this->configuration)));
    parent::preExecute();
  }
  
  public function executeCommit(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $prices = $request->getParameter('price');
    $cpt = 0;
    $this->json = array();
    
    foreach ( $prices as $gid => $gauge )
    {
      $manifestation = Doctrine::getTable('Manifestation')->createQuery('m', true)->leftJoin('m.Gauges g')->andWhere('g.id = ?', $gid)->fetchOne();
      $event = new sfEvent($this, 'pub.before_adding_tickets', array('manifestation' => $manifestation));
      
      foreach ( $gauge as $pid => $price )
      {
        if ( intval($price['quantity']) > 0 )
        {
          // cleaning up the transaction before testing if we can add the tickets
          foreach ( $this->getUser()->getTransaction()->Tickets as $key => $ticket )
          if ( $ticket->gauge_id == $gid && $ticket->price_id == $pid )
            unset($this->getUser()->getTransaction()->Tickets[$key]);
          
          $this->dispatcher->notify($event);
          
          // limitating the max tickets qty
          if ( $price['quantity'] > $event['max'] )
          {
            $price['quantity'] = $event['max'];
            $msg = __('We have limitated the quantity of tickets to %%max%% for %%manif%%.', array('%%max%%' => $event['max'], '%%manif%%' => $manifestation));
            if ( $request->getParameter('no_redirect') )
              $this->json['message'] = $msg;
            else
              $this->getUser()->setFlash('error', $msg);
          }
          
          // if it is impossible to add tickets
          if ( !$event->getReturnValue() )
          {
            if ( $request->getParameter('no_redirect') )
              $this->json['message'] = $event['message'];
            else
              $this->getUser()->setFlash('error', $event['message']);
            continue;
          }
        }
        
        $form = new PricesPublicForm($this->getUser()->getTransaction());
        $price['transaction_id'] = $this->getUser()->getTransaction()->id;
        
        $form->bind($price);
        if ( $form->isValid() )
        {
          $form->save();
          $cpt += $price['quantity'];
        }
        else
          error_log($form->getErrorSchema());
      }
    }
    
    $this->getUser()->setFlash('notice',__('%%nb%% ticket(s) added to your cart',array('%%nb%%' => $cpt)));
    if ( $request->getParameter('no_redirect') )
    {
      if ( sfConfig::get('sf_web_debug', false) && !$request->hasParameter('debug') )
        sfConfig::set('sf_web_debug', false);
      return 'Json';
    }
    $this->redirect('cart/show');
  }
}
