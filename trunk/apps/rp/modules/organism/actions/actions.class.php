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
  private $force_classic_template_dir = false;
  
  public function postExecute()
  {
    $this->addExtraRequirements();
    if ( !$this->useClassicTemplateDir() )
      $this->getContext()->getConfiguration()->changeTemplatesDir($this);
    return parent::postExecute();
  }
  protected function useClassicTemplateDir($bool = NULL)
  {
    if ( is_null($bool) )
      return $this->force_classic_template_dir;

    $this->force_classic_template_dir = $bool;
    return $this;
  }
  protected function addExtraRequirements()
  {
    if ( sfConfig::get('app_options_design') == 'tdp' )
    {
      if ( !isset($this->hasFilters) )
        $this->hasFilters = $this->getUser()->getAttribute('organism.filters', $this->configuration->getFilterDefaults(), 'admin_module');
      if ( !isset($this->filters) )
        $this->filters = $this->configuration->getFilterForm($this->getFilters());
      if ( !in_array($this->getActionName(), array('index','search','map','labels','csv')) )
        $this->setTemplate('edit');
    }
  }
  public function executeGroup(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/group.php');
  }
  public function executeGroupList(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/group-list.php');
  }
  public function executeBatchAddToGroup(sfWebRequest $request)
  {
    $request->checkCSRFProtection();
    
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $filters = $request->getParameter($this->getModuleName().'_filters');
    
    try {
      $validator = new sfValidatorDoctrineChoice(array('model' => 'Organism', 'multiple' => true, 'required' => false));
      $ids = $validator->clean($request->getParameter('ids'));
      $validator = new sfValidatorDoctrineChoice(array('model' => 'Group', 'multiple' => true));
      $groups = $request->getParameter('organism_filters');
      $groups = $validator->clean(isset($groups['groups_list'])
        ? $groups['groups_list']
        : $filters['groups_list']
      );
    }
    catch (sfValidatorError $e)
    {
      $this->getUser()->setFlash('error', 'A problem occurs when adding the selected items as some items do not exist anymore.');
      return $this->redirect('@organism');
    }
    
    if ( count($ids) > 0 )
    foreach ( $ids as $organism_id )
    foreach ( $groups as $group_id )
    {
      $go = new GroupOrganism;
      $go->organism_id = $organism_id;
      $go->group_id = $group_id;
      
      try { $go->save(); }
      catch(Doctrine_Exception $e) {}
    }
    
    $this->getUser()->setFlash('notice',__('The chosen organisms have been added to the selected groups.'));
    $this->redirect('@organism');
  }
  public function executeBatchAddProToGroup(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    $ids = $request->getParameter('ids');
    $groups = $request->getParameter('groups');
    
    $orgs = Doctrine::getTable('Organism')->createQuery('o')
      ->whereIn('o.id',$ids)
      ->execute();
    
    foreach ( $orgs as $organism )
    foreach ( $organism->Professionals as $pro )
    foreach ( $groups as $group_id )
    {
      $gp = new GroupProfessional();
      $gp->professional_id = $pro->id;
      $gp->group_id = $group_id;
      
      try { $gp->save(); }
      catch(Doctrine_Exception $e) {}
    }
    
    $this->getUser()->setFlash('notice',__('The contacts in chosen organisms have been added to the selected groups.'));
    $this->redirect('group/show?id='.$gp->group_id);
  }

  public function executeEmailList(sfWebRequest $request)
  {
    if ( !$request->getParameter('id') )
      $this->forward('organism','index');
    
    $this->email_id = $request->getParameter('id');
    $q = Doctrine::getTable('Organism')->createQueryByEmailId($this->email_id);
    
    $this->pager = $this->configuration->getPager('Organism');
    $this->pager->setMaxPerPage(15);
    $this->pager->setQuery($q);
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $this->pager->init();
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
    $this->executeShow($request);
    
    if ( sfConfig::get('app_options_design') != 'tdp' && !$this->getUser()->hasCredential('pr-organism-edit') )
      $this->setTemplate('show');
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

  public function executeMap(sfWebRequest $request)
  {
    $q = $this->buildQuery();
    $this->gMap = new GMap();
    if ( !$this->gMap->getGMapClient()->getAPIKey() )
    {
      $this->getUser()->setFlash('error',__("The geolocalization module is not enabled, you can't access this function."));
      $this->redirect('index');
    }
    $this->gMap = Addressable::getGmapFromQuery($q,$request);
  }
  
  public function executeSearch(sfWebRequest $request)
  {
    self::executeIndex($request);
    
    $search = $this->sanitizeSearch($request->getParameter('s'));
    $transliterate = sfContext::getInstance()->getConfiguration()->transliterate;
    
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $table = Doctrine_Core::getTable('Organism');
    $this->pager->setQuery($table->search($search.'*',$this->pager->getQuery()));

    $this->addExtraRequirements();
    
    $this->pager->init();
    $this->setTemplate('index');
  }
  
  public function executeCsv(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/csv.php');
  }
  public function executeLabels(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/labels.php');
  }
  
  public static function sanitizeSearch($search)
  {
    $nb = strlen($search);
    $charset = sfContext::getInstance()->getConfiguration()->charset;
    return strtolower(iconv($charset['db'],$charset['ascii'],substr($search,$nb-1,$nb) == '*' ? substr($search,0,$nb-1) : $search));
  }
}
