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
  public function executeEdit(sfWebRequest $request)
  {
    parent::executeEdit($request);
    
    if ( !$this->getUser()->hasCredential('pr-organism-edit') )
      $this->setTemplate('show');
  }

  public function executeSearchIndexing(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    $table = Doctrine_Core::getTable('Organism');
    $table->getTemplate('Doctrine_Template_Searchable')->getPlugin()
      ->setOption('analyzer', new MySearchAnalyzer());
    $table->batchUpdateIndex($nb = 1500);
    
    $this->getUser()->setFlash('notice',__('%nb% record(s) have been indexed',array('%nb%' => $nb)));
    $this->executeIndex($request);
    $this->setTemplate('index');
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    parent::executeIndex($request);
    if ( !$this->sort[0] )
    {
      $this->sort = array('name','');
      $this->pager->getQuery()->orderby('name');
    }
  }

  public function executeShow(sfWebRequest $request)
  {
    $q = Doctrine::getTable('Organism')->createQuery();
    $q->where('id = ?',$request->getParameter('id'))
      ->orderBy('c.name, c.firstname, pt.name, p.name');
    $organisms = $q->execute();
    
    $this->organism = $organisms[0];
    $this->forward404Unless($this->organism);
    $this->form = $this->configuration->getForm($this->organism);
  }
  public function executeEdit(sfWebRequest $request)
  {
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
    $charset = sfContext::getInstance()->getConfiguration()->charset;
    $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q'));
    
    $q = Doctrine::getTable('Organism')
      ->createQuery()
      ->orderBy('name')
      ->limit($request->getParameter('limit'));
    if ( $request->getParameter('email') == 'true' )
    $q->andWhere("email IS NOT NULL AND email != ?",'');
    $q = Doctrine_Core::getTable('Organism')
      ->search($search.'*',$q);
    $request = $q->execute()->getData();

    $organisms = array();
    foreach ( $request as $organism )
      $organisms[$organism->id] = (string) $organism;
    
    return $this->renderText(json_encode($organisms));
  }

  protected function addViewRenderer()
  {
    $response = $this->getResponse()->addStyleSheet('view');
    $response = $this->getResponse()->addJavaScript('more-simple');
  }
}

