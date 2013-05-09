<?php

require_once dirname(__FILE__).'/../lib/gaugeGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/gaugeGeneratorHelper.class.php';

/**
 * gauge actions.
 *
 * @package    e-venement
 * @subpackage gauge
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class gaugeActions extends autoGaugeActions
{
  public function executeState(sfWebRequest $request)
  {
    parent::executeShow($request);
    $this->setLayout('nude');
    
    if ( $request->hasParameter('debug') )
      sfConfig::set('sf_web_debug', true);
    
    if ( $request->hasParameter('json') )
    {
      $arr = array(
        'id' => $this->gauge->id,
        'workspace' => (string)$this->gauge->Workspace,
        'total' => $this->gauge->value,
        'free' => $this->gauge->value - ($this->gauge->printed + $this->gauge->ordered + (sfConfig::get('project_tickets_count_demands',false) ? $this->gauge->asked : 0)),
        'booked' => array(
          'printed' => $this->gauge->printed,
          'ordered' => $this->gauge->ordered,
          'asked' => sfConfig::get('project_tickets_count_demands',false) ? $this->gauge->asked : 0,
        ),
      );
      
      return $this->renderText(json_encode($arr));
    }
  }
  
  public function executeBatchEdit(sfWebRequest $request)
  {
    if ( intval($mid = $request->getParameter('id')).'' != $request->getParameter('id') )
      throw new sfError404Exception();
    
    $q = Doctrine::getTable('Gauge')->createQuery('g')
      ->leftJoin('g.Workspace w')
      ->leftJoin('w.Order o ON o.workspace_id = w.id AND o.sf_guard_user_id = '.intval($this->getUser()->getId()))
      ->andWhere('g.manifestation_id = ?',$mid)
      ->orderBy('o.rank, w.name');
    $this->sort = array('Workspace','');
    
    $this->pager = $this->configuration->getPager('Gauge');
    $this->pager->setQuery($q);
    $this->pager->setPage($request->getParameter('page'));
    $this->pager->init();
    
    $this->hasFilters = $this->getUser()->getAttribute('gauge.list_filters', $this->configuration->getFilterDefaults(), 'admin_module');
  }
}
