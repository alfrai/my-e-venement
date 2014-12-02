<?php

require_once dirname(__FILE__).'/../lib/member_card_typeGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/member_card_typeGeneratorHelper.class.php';

/**
 * member_card_type actions.
 *
 * @package    symfony
 * @subpackage member_card_type
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class member_card_typeActions extends autoMember_card_typeActions
{
  public function executeShow(sfWebRequest $request)
  {
    $this->redirect('member_card_type/edit?id='.$request->getParameter('id'));
  }
}
