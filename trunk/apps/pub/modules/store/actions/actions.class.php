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
  
  public function executeMod(sfWebRequest $request)
  {
    return require(__DIR__.'/mod.php');
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    $this->setFilters(array());
    $this->getUser()->setDefaultCulture($request->getLanguages());
    // continue normal operations
    parent::executeIndex($request);
  }
  public function executeEdit(sfWebRequest $request)
  {
    $condition = array();
    foreach ( $conditions = array_keys($this->getUser()->getMetaEventsCredentials()) as $null )
      $condition[] = '?';
    $condition = implode(',', $condition);
    
    $conditions[] = $this->getUser()->getId();
    $q = Doctrine::getTable('Product')->createQuery('p')
      ->andWhere('p.id = ?', $request->getParameter('id'))
      ->leftJoin('p.Category pc')
      ->andWhere('pc.online = ?', true)
      ->leftJoin("p.LinkedProducts lp WITH lp.meta_event_id IN ($condition) AND (SELECT count(lpp.id) FROM Price lpp LEFT JOIN lpp.Users lppu WHERE lpp.id IN (SELECT lppp.price_id FROM PriceProduct lppp WHERE lppp.product_id = lp.id) AND lppu.id = ?) > 0", $conditions)
      ->leftJoin("p.LinkedManifestations lm WITH lm.id IN (SELECT lmm.id FROM Manifestation lmm LEFT JOIN lmm.Event lme WHERE lme.meta_event_id IN ($condition)) AND (SELECT count(lmp.id) FROM Price lmp LEFT JOIN lmp.Users lmpu WHERE lmp.id IN (SELECT lmpm.price_id FROM PriceManifestation lmpm WHERE lmpm.manifestation_id = lm.id) AND lmpu.id = ?) > 0", $conditions)
      ->leftJoin('p.PriceProducts pp')
      ->leftJoin('pp.Price price')
      ->orderBy('pp.value DESC')
    ;
    $this->forward404If(!( $this->product = $q->fetchOne() ));
  }
  public function executeShow(sfWebRequest $request)
  {
    $this->forward('store', 'edit');
  }
  
  /* disabled actions */
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
  
  // FILTERS
  protected function getFilters()
  {
    return $this->getUser()->getAttribute('store.filters', $this->configuration->getFilterDefaults(), 'pub_module');
  }
  protected function setFilters(array $filters)
  {
    return $this->getUser()->setAttribute('store.filters', $filters, 'pub_module');
  }
}
