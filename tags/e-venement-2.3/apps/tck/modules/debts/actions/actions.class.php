<?php

require_once dirname(__FILE__).'/../lib/debtsGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/debtsGeneratorHelper.class.php';

/**
 * debts actions.
 *
 * @package    symfony
 * @subpackage debts
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class debtsActions extends autoDebtsActions
{
  public function executeShow(sfWebRequest $request)
  {
    parent::executeShow($request);
    $this->redirect('ticket/sell?id='.$this->transaction->id);
  }
}
