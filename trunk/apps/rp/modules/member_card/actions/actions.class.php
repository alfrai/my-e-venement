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
  public function executeIndex(sfWebRequest $request)
  {
    $this->request = $request;
    parent::executeIndex($request);
  }
  protected function getPager()
  {
    $q = $this->buildQuery();
    if ( isset($this->request) && $this->request->hasParameter('contact_id') )
    {
      $this->forward404Unless(intval($this->request->getParameter('contact_id')) > 0 );
      
      $a = $q->getRootAlias();
      $q->andWhere("$a.contact_id = ?",$this->request->getParameter('contact_id'));
    }
    
    $pager = $this->configuration->getPager('MemberCard');
    $pager->setQuery($q);
    $pager->setPage($this->getPage());
    $pager->init();
    
    return $pager;
  }
  
  public function executeDelete(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $request->checkCSRFProtection();

    $this->dispatcher->notify(new sfEvent($this, 'admin.delete_object', array('object' => $this->getRoute()->getObject())));
   
    $this->card = $this->getRoute()->getObject();
    $this->contact = $this->card->Contact;
    $this->transaction_id = $this->card->Payments->count() > 0 ? $this->card->Payments[0]->transaction_id : NULL;
    
    try {
      $this->card->delete();
    }
    catch ( liEvenementException $e )
    {
      $this->getUser()->setFlash('error',__('This member card has been used to print tickets'));
      return $this->redirect('contact/card?id='.$this->contact->id);
    }
    
    $this->getUser()->setFlash('notice', 'The item was deleted successfully.');
    
    if ( is_null($this->transaction_id) )
      $this->redirect('contact/card?id='.$this->contact->id);
    else
    {
      $this->getContext()->getConfiguration()->loadHelpers('CrossAppLink');
      $this->redirect(cross_app_url_for('tck','ticket/pay?id='.$this->transaction_id));
    }
  }
}
