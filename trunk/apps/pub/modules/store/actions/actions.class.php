<?php

/**
 * store actions.
 *
 * @package    e-venement
 * @subpackage store
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class storeActions extends sfActions
{
  public function preExecute()
  {
    $this->getUser()->setFlash('error', 'Page not found');
    if ( !$this->getUser()->isStoreActive() )
      $this->redirect('homepage');
  }
  public function executeIndex(sfWebRequest $request)
  {
    $this->getUser()->setFlash('error', 'Store: Work In Progress...');
    $this->redirect('homepage');
  }
}
