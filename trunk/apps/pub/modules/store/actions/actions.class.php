<?php

require_once dirname(__FILE__).'/../lib/storeGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/storeGeneratorHelper.class.php';

/**
 * store actions.
 *
 * @package    e-venement
 * @subpackage store
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class storeActions extends autoStoreActions
{
  public function preExecute()
  {
    if ( !$this->getUser()->isStoreActive() )
    {
      $this->getUser()->setFlash('error', 'Page not found');
      $this->redirect('homepage');
    }
    $this->dispatcher->notify(new sfEvent($this, 'pub.pre_execute', array('configuration' => $this->configuration)));
    parent::preExecute();
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    $this->setFilters(array());
    $this->getUser()->setDefaultCulture($request->getLanguages());
    // continue normal operations
    parent::executeIndex($request);
  }
  public function executeShow(sfWebRequest $request)
  {
    $this->forward('store', 'edit');
  }
  public function executeBatchDelete(sfWebRequest $request)
  {
    $this->redirect('store/index');
  }
  public function executeCreate(sfWebRequest $request)
  {
    $this->executeBatchDelete($request);
  }
  public function executeNew(sfWebRequest $request)
  {
    $this->executeBatchDelete($request);
  }
  public function executeUpdate(sfWebRequest $request)
  {
    $this->executeEdit($request);
  }
  protected function getFilters()
  {
    return $this->getUser()->getAttribute('store.filters', $this->configuration->getFilterDefaults(), 'pub_module');
  }
  protected function setFilters(array $filters)
  {
    return $this->getUser()->setAttribute('store.filters', $filters, 'pub_module');
  }
}
