<?php

/**
 * default actions.
 *
 * @package    e-venement
 * @subpackage default
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class defaultActions extends sfActions
{
  protected function addViewRenderer()
  {
    $response = $this->getResponse()->addStyleSheet('/sfAdminThemejRollerPlugin/css/reset.css');
  }

 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->addViewRenderer();
  }
}
