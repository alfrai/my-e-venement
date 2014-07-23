<?php

require_once dirname(__FILE__).'/../lib/filterGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/filterGeneratorHelper.class.php';

/**
 * filter actions.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class filterActions extends autoFilterActions
{
  public function executeIndex(sfWebRequest $request)
  {
    if ( $request->getParameter('type',false) )
    {
      $this->setFilters(array_merge($this->configuration->getFilterDefaults(), array(
        'type' => array('text' => strtolower($request->getParameter('type'))),
      )));
    }
    
    parent::executeIndex($request);
  }
  
  public function executeShow(sfWebRequest $request)
  {
    parent::executeShow($request);
    $this->getUser()->setAttribute($this->filter->type.'.filters', unserialize($this->filter->filter), 'admin_module');
  }
}
