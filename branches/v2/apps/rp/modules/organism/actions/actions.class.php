<?php

require_once dirname(__FILE__).'/../lib/organismGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/organismGeneratorHelper.class.php';

/**
 * organism actions.
 *
 * @package    e-venement
 * @subpackage organism
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class organismActions extends autoOrganismActions
{
  protected function addViewRenderer()
  {
    $response = $this->getResponse()->addStyleSheet('view');
    $response = $this->getResponse()->addJavaScript('more-simple');
  }
  
  public function executeShow(sfWebRequest $request)
  {
    $this->addViewRenderer();
    //parent::executeShow($request);
    
    $q = Doctrine::getTable('Organism')->createQuery();
    $q->where('id = ?',$request->getParameter('id'))
      ->orderBy('c.name, c.firstname, pt.name, p.name');
    $organisms = $q->execute();
    
    $this->organism = $organisms[0];
    $this->forward404Unless($this->organism);
    $this->form = $this->configuration->getForm($this->organism);
  }
  public function executeUpdate(sfWebRequest $request)
  {
    $this->addViewRenderer();
    return parent::executeUpdate($request);
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->addViewRenderer();
    //return parent::executeEdit($request);
    
    $q = Doctrine::getTable('Organism')->createQuery();
    $q->where('id = ?',$request->getParameter('id'))
      ->orderBy('c.name, c.firstname, pt.name, p.name');
    $organisms = $q->execute();
    
    $this->organism = $organisms[0];
    $this->forward404Unless($this->organism);
    $this->form = $this->configuration->getForm($this->organism);
  }
  
  public function executeAjax(sfWebRequest $request)
  {
    $this->getResponse()->setContentType('application/json');
    $request = Doctrine::getTable('Organism')->createQuery()
      ->where('name ILIKE ?',array('%'.$request->getParameter('q').'%'))
      ->limit($request->getParameter('limit'))
      ->execute()
      ->getData();
    
    $organisms = array();
    foreach ( $request as $organism )
      $organisms[$organism->id] = (string) $organism;
    
    return $this->renderText(json_encode($organisms));
  }
}

