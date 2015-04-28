<?php

require_once dirname(__FILE__).'/../lib/checkpointGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/checkpointGeneratorHelper.class.php';

/**
 * checkpoint actions.
 *
 * @package    e-venement
 * @subpackage checkpoint
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class checkpointActions extends autoCheckpointActions
{
  public function executeNew(sfWebRequest $request)
  {
    parent::executeNew($request);
    
    $eid = NULL;
    if ( $request->getParameter('event') )
    {
      $event = Doctrine::getTable('Event')->findOneBySlug($request->getParameter('event'));
      $eid = $event->id;
    }
    else if ( intval($request->getParameter('id')) > 0 )
      $eid = intval($request->getParameter('id'));
    
    $this->form->getWidget('event_id')->setDefault($eid);
  }
  
  public function executeBatchEdit(sfWebRequest $request)
  {
    if ( intval($mid = $request->getParameter('id')).'' != $request->getParameter('id') )
      throw new sfError404Exception();
    
    $q = Doctrine::getTable('Checkpoint')->createQuery('c')
      ->andWhere('c.event_id = ?',$mid)
      ->orderBy('c.name');
    $this->sort = array('name','');
    
    $this->pager = $this->configuration->getPager('Checkpoint');
    $this->pager->setMaxPerPage(10);
    $this->pager->setQuery($q);
    $this->pager->setPage($request->getParameter('page'));
    $this->pager->init();
    
    $this->hasFilters = $this->getUser()->getAttribute('checkpoint.list_filters', $this->configuration->getFilterDefaults(), 'admin_module');
  }

  public function executeDelete(sfWebRequest $request)
  {
    $request->checkCSRFProtection();

    $this->dispatcher->notify(new sfEvent($this, 'admin.delete_object', array('object' => $this->getRoute()->getObject())));

    $this->getRoute()->getObject()->delete();

    $this->getUser()->setFlash('notice', 'The item was deleted successfully.');

    $this->redirect('checkpoint/new');
  }
}
