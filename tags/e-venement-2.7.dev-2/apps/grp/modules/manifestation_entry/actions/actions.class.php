<?php

require_once dirname(__FILE__).'/../lib/manifestation_entryGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/manifestation_entryGeneratorHelper.class.php';

/**
 * manifestation_entry actions.
 *
 * @package    e-venement
 * @subpackage manifestation_entry
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class manifestation_entryActions extends autoManifestation_entryActions
{
  public function executeShow(sfWebRequest $request)
  {
    $this->manifestation_entry = $this->getRoute()->getObject();
  }
  
  public function executeNew(sfWebRequest $request)
  {
    parent::executeNew($request);
    
    if ( $request->hasParameter('event_id') )
      $this->form->restrictToEvent($request->getParameter('event_id'));
  }
  
  public function executeDel(sfWebRequest $request)
  {
    $this->getRoute()->getObject()->delete();
    return $this->redirect($this->getModuleName());
  }
}
