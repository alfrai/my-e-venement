<?php

/**
 * card actions.
 *
 * @package    symfony
 * @subpackage card
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class cardActions extends sfActions
{
  public function preExecute()
  {
    $this->dispatcher->notify(new sfEvent($this, 'pub.pre_execute', array('configuration' => $this->configuration)));
    parent::preExecute();
  }
  public function executeIndex(sfWebRequest $request)
  {
    $this->redirectIfNotAuthenticated();
    
    $this->member_card_types = Doctrine::getTable('MemberCardType')->createQuery('mct')
      ->leftJoin('mct.Users u')
      ->andWhere('u.id = ?',$this->getUser()->getId())
      ->orderBy('name')
      ->execute();
    
    $this->mct = array();
    if ( $this->getUser()->getTransaction()->MemberCards->count() > 0 )
    foreach ( $this->getUser()->getTransaction()->MemberCards as $mc )
    {
      if ( !isset($this->mct[$mc->member_card_type_id]) )
        $this->mct[$mc->member_card_type_id] = 0;
      $this->mct[$mc->member_card_type_id]++;
    }
  }
  
  public function executeOrder(sfWebRequest $request)
  {
    $this->redirectIfNotAuthenticated();
    
    // empty'ing member cards from transaction
    $this->getUser()->getTransaction()->MemberCards->delete();
    
    $order = $request->getParameter('member_card_type');
    foreach ( $order as $id => $qty )
    if ( intval($qty) > 0 && intval($qty) < sfConfig::get('app_member_cards_max_per_transaction', 3) )
    for ( $i = 0 ; $i < intval($qty) ; $i++ )
      $this->getContext()->getConfiguration()->addMemberCard($this->getUser()->getTransaction(), $id);
    
    $this->redirect('cart/show');
  }
  
  protected function isAuthenticated()
  {
    try { return $this->getUser()->getContact(); }
    catch ( liEvenementException $e )
    { return false; }
  }
  protected function redirectIfNotAuthenticated()
  {
    if ( self::isAuthenticated() )
      return true;

    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->getUser()->setFlash('error',__('To order member cards, you need to be authenticated'));
    $this->redirect('login/index');
    return false;
  }
}
