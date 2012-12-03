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
    
    foreach ( $prices as $gauge )
    foreach ( $gauge as $price )
    {
      $form = new PricesPublicForm;
      $price['transaction_id'] = $this->getUser()->hasAttribute('transaction_id')
        ? $this->getUser()->getAttribute('transaction_id')
        : NULL;
      
      $form->bind($price);
      if ( $form->isValid() )
      {
        if ( $price['transaction_id'] )
          $form->updateObject(array('id' => $price['transaction_id']));
        
        $transaction = $form->save();
        $this->getUser()->setAttribute('transaction_id',$transaction->id);
        
        $cpt += $price['quantity'];
      }
    }
    
    $this->getUser()->setFlash('notice',__('%%nb%% ticket(s) have been added to your cart'));
    $this->redirect('cart/show');
  }
}
