<?php

require_once dirname(__FILE__).'/../lib/meta_eventGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/meta_eventGeneratorHelper.class.php';

/**
 * meta_event actions.
 *
 * @package    e-venement
 * @subpackage meta_event
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class meta_eventActions extends autoMeta_eventActions
{
  public function preExecute()
  {
    $this->dispatcher->notify(new sfEvent($this, 'pub.pre_execute', array('configuration' => $this->configuration)));
    parent::preExecute();
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('Slug');
    $this->getUser()->setDefaultCulture($request->getLanguages());
    
    parent::executeIndex($request);
    
    // only one meta_event...
    if ( $this->pager->getNbResults() == 1 )
    {
      foreach ( array('success', 'notice', 'error') as $type )
      if ( $this->getUser()->getFlash($type) )
        $this->getUser()->setFlash($type, $this->getUser()->getFlash($type));
      $this->redirect('event/index?meta_event='.slugify($this->pager->getCurrent()->name));
    }
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('Slug');
    parent::executeEdit($request);
    $this->redirect('event/index?meta_event='.$this->meta_event->slug);
  }
  public function executeBatchDelete(sfWebRequest $request)
  {
    $this->redirect('event/index');
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
    return $this->getUser()->getAttribute('meta_event.filters', $this->configuration->getFilterDefaults(), 'pub_module');
  }

  protected function setFilters(array $filters)
  {
    return $this->getUser()->setAttribute('meta_event.filters', $filters, 'pub_module');
  }
}
