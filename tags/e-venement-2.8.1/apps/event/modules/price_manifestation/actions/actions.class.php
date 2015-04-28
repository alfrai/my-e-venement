<?php

require_once dirname(__FILE__).'/../lib/price_manifestationGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/price_manifestationGeneratorHelper.class.php';

/**
 * price_manifestation actions.
 *
 * @package    e-venement
 * @subpackage price_manifestation
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class price_manifestationActions extends autoPrice_manifestationActions
{
  public function executeBatchEdit(sfWebRequest $request)
  {
    if ( intval($mid = $request->getParameter('id')).'' != $request->getParameter('id') )
      throw new sfError404Exception();
    
    $q = Doctrine::getTable('PriceManifestation')->createQuery('pm')
      ->leftJoin('pm.Price p')
      ->leftJoin("p.Translation pt WITH pt.lang = '".$this->getUser()->getCulture()."'")
      ->where('manifestation_id = ?',$mid)
      ->orderBy('pm.value DESC, pt.name');
    $this->sort = array('value','desc');
    
    $this->pager = $this->configuration->getPager('PriceManifestation');
    $this->pager->setQuery($q);
    $this->pager->setPage($request->getParameter('page'));
    $this->pager->init();
    
    $this->hasFilters = $this->getUser()->getAttribute('price_manifestation.list_filters', $this->configuration->getFilterDefaults(), 'admin_module');
  }
}
