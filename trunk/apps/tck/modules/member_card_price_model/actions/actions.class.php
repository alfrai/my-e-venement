<?php

require_once dirname(__FILE__).'/../lib/member_card_price_modelGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/member_card_price_modelGeneratorHelper.class.php';

/**
 * member_card_price_model actions.
 *
 * @package    symfony
 * @subpackage member_card_price_model
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class member_card_price_modelActions extends autoMember_card_price_modelActions
{
  public function executeCreate(sfWebRequest $request)
  {
    try { parent::executeCreate($request); }
    catch ( Doctrine_Connection_Exception $e ) {
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('error', __('You might have tried to create a price association that was already existing. Please check the list, try some filters...'));
      $this->redirect('member_card_price_model/index');
    }
  }
}
