<?php

require_once dirname(__FILE__).'/../lib/member_cardGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/member_cardGeneratorHelper.class.php';

/**
 * member_card actions.
 *
 * @package    e-venement
 * @subpackage member_card
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class member_cardActions extends autoMember_cardActions
{
  public function executeDelete(sfWebRequest $request)
  {
    $request->checkCSRFProtection();

    $this->dispatcher->notify(new sfEvent($this, 'admin.delete_object', array('object' => $this->getRoute()->getObject())));
   
    $this->card = $this->getRoute()->getObject();
    $this->contact = $this->card->Contact;
    $this->transaction_id = $this->card->Payments->count() > 0 ? $this->card->Payments[0]->transaction_id : NULL;
    
    // exception, if there are tickets linked with this member card
    $tickets = Doctrine::getTable('Ticket')->createQuery('tck')
      ->andWhere('tck.printed = true')
      ->andWhere('tck.member_card_id = ?',$this->card->id)
      ->execute();
    if ( $tickets->count() > 0 )
    {
      $this->getUser()->setFlash('error','This member card has been used to print tickets');
      return $this->redirect('contact/card?id='.$this->contact->id);
    }
    
    if ($this->card->delete())
    {
      $this->getUser()->setFlash('notice', 'The item was deleted successfully.');
    }
    
    if ( is_null($this->transaction_id) )
      $this->redirect('contact/card?id='.$this->contact->id);
    else
    {
      $this->getContext()->getConfiguration()->loadHelpers('CrossAppLink');
      $this->redirect(cross_app_url_for('tck','ticket/pay?id='.$this->transaction_id));
    }
  }
}
