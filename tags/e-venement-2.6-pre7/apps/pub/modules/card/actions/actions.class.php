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
    if ( intval($qty) > 0 )
    for ( $i = 0 ; $i < intval($qty) ; $i++ )
    {
      $mcf = new MemberCardForm;
      $arr = array();
      
      $arr['member_card_type_id'] = $id;
      $arr['created_at'] = date('Y-m-d');
      $arr['transaction_id'] = $this->getUser()->getTransaction()->id;
      $arr['contact_id'] = $this->getUser()->getTransaction()->contact_id;
      $arr['active'] = false;
      $arr[$mcf->getCSRFFieldName()] = $mcf->getCSRFToken();
      
      $arr['expire_at'] = sfConfig::has('project_cards_expiration_delay')
        ? date('Y-m-d H:i:s',strtotime(sfConfig::get('project_cards_expiration_delay')))
        : (strtotime(date('Y').'-'.sfConfig::get('project_cards_expiration_date')) > strtotime('now')
          ? date('Y').'-'.sfConfig::get('project_cards_expiration_date')
          : (date('Y')+1).'-'.sfConfig::get('project_cards_expiration_date'));
      
      $mcf->bind($arr);
      
      if ( !$mcf->isValid() )
        throw new liEvenementException('Error when adding member cards.');
      
      $mcf->save();
    }
    
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
