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
  public function executeBatchEdit(sfWebRequest $request)
  {
    if ( intval($mid = $request->getParameter('id')).'' != $request->getParameter('id') )
      throw new sfError404Exception();
    
    $q = Doctrine::getTable('Gauge')->createQuery('g')
      ->leftJoin('g.Workspace w')
      ->andWhere('g.manifestation_id = ?',$mid)
      ->orderBy('w.name');
    $this->sort = array('Workspace','');
    
    $this->pager = $this->configuration->getPager('Gauge');
    $this->pager->setQuery($q);
    $this->pager->setPage($request->getParameter('page'));
    $this->pager->init();
    
    $this->hasFilters = $this->getUser()->getAttribute('gauge.list_filters', $this->configuration->getFilterDefaults(), 'admin_module');
  }
}
