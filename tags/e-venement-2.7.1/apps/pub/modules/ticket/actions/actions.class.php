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
    
    foreach ( $prices as $gid => $gauge )
    {
      $manifestation = Doctrine::getTable('Manifestation')->createQuery('m', true)->leftJoin('m.Gauges g')->andWhere('g.id = ?', $gid)->fetchOne();
      $this->dispatcher->notify($event = new sfEvent($this, 'pub.before_adding_tickets', array('manifestation' => $manifestation)));
      
      if ( $event->getReturnValue() )
      foreach ( $gauge as $price )
      {
        $form = new PricesPublicForm($this->getUser()->getTransaction());
        $price['transaction_id'] = $this->getUser()->getTransaction()->id;
        
        $form->bind($price);
        if ( $form->isValid() )
        {
          error_log('valid');
          $form->save();
          $cpt += $price['quantity'];
        }
        else
          error_log($form->getErrorSchema());
      }
      else
        $this->getUser()->setFlash('error', $event['message']);
    }
    
    $this->getUser()->setFlash('notice',__('%%nb%% ticket(s) have been added to your cart',array('%%nb%%' => $cpt)));
    $this->redirect('cart/show');
  }
}
