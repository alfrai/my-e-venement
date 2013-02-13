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
  public function executeCommit(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $prices = $request->getParameter('price');
    $cpt = 0;
    
    // cleaning of tickets
    $q = Doctrine_Query::create()->from('Ticket tck')
      ->andWhere('tck.transaction_id = ?', $this->getUser()->getTransaction()->id)
      ->andWhereIn('tck.gauge_id',array_keys($prices))
      ->delete();
    $q->execute();
    $this->getUser()->getTransaction()->Tickets->clear();
    
    foreach ( $prices as $gauge )
    foreach ( $gauge as $price )
    {
      $form = new PricesPublicForm($this->getUser()->getTransaction());
      $price['transaction_id'] = $this->getUser()->getTransaction()->id;
      
      if ( $price['quantity'] == 0 )
        continue;
      
      $form->bind($price);
      if ( $form->isValid() )
      {
        $form->save();
        $cpt += $price['quantity'];
      }
    }
    
    $this->getUser()->setFlash('notice',__('%%nb%% ticket(s) have been added to your cart',array('%%nb%%' => $cpt)));
    $this->redirect('cart/show');
  }
}
