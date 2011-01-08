<?php

require_once dirname(__FILE__).'/../lib/groupGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/groupGeneratorHelper.class.php';

/**
 * group actions.
 *
 * @package    e-venement
 * @subpackage group
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class groupActions extends autoGroupActions
{
  protected function addViewRenderer()
  {
    $response = $this->getResponse()->addStyleSheet('view');
    $response = $this->getResponse()->addJavaScript('more-simple');
  }
  
  public function executeUpdate(sfWebRequest $request)
  {
    $this->addViewRenderer();
    return parent::executeUpdate($request);
  }
  public function executeShow(sfWebRequest $request)
  {
    $this->addViewRenderer();
    return parent::executeShow($request);
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->addViewRenderer();
    return parent::executeEdit($request);
  }
}
