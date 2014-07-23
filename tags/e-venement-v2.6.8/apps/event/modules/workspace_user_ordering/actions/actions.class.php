<?php

require_once dirname(__FILE__).'/../lib/workspace_user_orderingGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/workspace_user_orderingGeneratorHelper.class.php';

/**
 * workspace_user_ordering actions.
 *
 * @package    symfony
 * @subpackage workspace_user_ordering
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class workspace_user_orderingActions extends autoWorkspace_user_orderingActions
{
  public function executeEdit(sfWebRequest $request)
  {
    $this->redirect('@workspace_user_ordering');
  }
  public function executeShow(sfWebRequest $request)
  {
    $this->executeEdit($request);
  }
}
