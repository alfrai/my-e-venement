<?php

require_once dirname(__FILE__).'/../lib/contactGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/contactGeneratorHelper.class.php';

/**
 * contact actions.
 *
 * @package    e-venement
 * @subpackage contact
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class contactActions extends autoContactActions
{
  protected function addIndexRenderer()
  {
    $this->getResponse()->addJavaScript('contact-filters');
  }
  protected function addViewRenderer()
  {
    $this->getResponse()->addStyleSheet('view');
    $this->getResponse()->addStyleSheet('/sfFormExtraPlugin/css/jquery.autocompleter.css');
  }
  
  public function executeUpdate(sfWebRequest $request)
  {
    $this->addViewRenderer();
    return parent::executeUpdate($request);
  }
  public function executeShow(sfWebRequest $request)
  {
    $this->addViewRenderer();
    return parent::executeShow($request);
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->addViewRenderer();
    return parent::executeEdit($request);
  }
  
  public function executeAjax(sfWebRequest $request)
  {
    $this->getResponse()->setContentType('application/json');
    $request = Doctrine::getTable('Contact')->createQuery()
      ->where("name ILIKE ? OR firstname ILIKE ?",array('%'.$request->getParameter('q').'%','%'.$request->getParameter('q').'%'))
      ->orderBy('name, firstname')
      ->limit($request->getParameter('limit'))
      ->execute()
      ->getData();
    
    $contacts = array();
    foreach ( $request as $contact )
      $contacts[$contact->id] = (string) $contact;
    
    return $this->renderText(json_encode($contacts));
  }
  public function executeCsv(sfWebRequest $request)
  {
    $this->pager     = $this->getPager();
    if ( $this->pager->count() > 4000 )
    {
      $this->getUser()->setFlash('csv_max_num_records',__("You can't export more than 4000 records a time, try with a smaller set of records."));
      $this->forward('contact','index');
    }
    
    $this->delimiter = $request->hasParameter('ms') ? ';' : ',';
    $this->enclosure = '"';
    $this->charset   = sfContext::getInstance()->getConfiguration()->charset;
    
    $this->options   = array(
      'ms'        => $request->hasParameter('ms'),
      'nopro'     => $request->hasParameter('nopro'),
      'noheader'  => $request->hasParameter('noheader'),
    );
    
    sfConfig::set('sf_web_debug', false);
    sfConfig::set('sf_escaping_strategy', false);
    sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    
    $this->getResponse()->clearHttpHeaders();
    $this->getResponse()->setContentType('text/comma-separated-values');
    $this->getResponse()->addHttpMeta('content-disposition', 'attachment; filename="'.$this->getModuleName().'s.csv"',true);
    $this->getResponse()->sendHttpHeaders();
    $this->outstream = 'php://output';
    
    $this->setLayout(false);
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    $this->addIndexRenderer();
    parent::executeIndex($request);
  }
  public function executeFilter(sfWebRequest $request)
  {
    $this->addIndexRenderer();
    //print_r($this->getUser()->getAttribute('contact.filters', $this->configuration->getFilterDefaults(), 'admin_module'));
    //print_r($request->hasParameter($this->configuration->getFilterForm($this->getFilters())->getName()));
    parent::executeFilter($request);
  }
}
