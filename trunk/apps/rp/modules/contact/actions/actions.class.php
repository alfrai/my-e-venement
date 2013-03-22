<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2011 Ayoub HIDRI <ayoub.hidri AT gmail.com>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
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
  public function executeError404(sfWebRequest $request)
  {
  }
  public function executeBatchAddToGroup(sfWebRequest $request)
  {
    $request->checkCSRFProtection();
    
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $filters = $request->getParameter($this->getModuleName().'_filters');
    
    try {
      $validator = new sfValidatorDoctrineChoice(array('model' => 'Contact', 'multiple' => true, 'required' => false));
      $ids = $validator->clean($request->getParameter('ids'));
      $validator = new sfValidatorDoctrineChoice(array('model' => 'Professional', 'multiple' => true, 'required' => false));
      $pro_ids = $validator->clean($request->getParameter('pro_ids'));
      $validator = new sfValidatorDoctrineChoice(array('model' => 'Group', 'multiple' => true));
      $groups = $request->getParameter('contact_filters');
      $groups = $validator->clean(isset($groups['groups_list'])
        ? $groups['groups_list']
        : $filters['groups_list']
      );
    }
    catch (sfValidatorError $e)
    {
      $this->getUser()->setFlash('error', 'A problem occurs when adding the selected items as some items do not exist anymore.');
      return $this->redirect('@contact');
    }
    
    // contacts
    if ( count($ids) > 0 )
    foreach ( $ids as $contact_id )
    foreach ( $groups as $group_id )
    {
      $gc = new GroupContact;
      $gc->contact_id = $contact_id;
      $gc->group_id = $group_id;
      
      try { $gc->save(); }
      catch(Doctrine_Exception $e) {}
    }
    
    // professionals
    if ( count($pro_ids) > 0 )
    foreach ( $pro_ids as $pro_id )
    foreach ( $groups as $group_id )
    if ( intval($group_id).'' === $group_id.'' )
    {
      $gc = new GroupProfessional();
      $gc->professional_id = $pro_id;
      $gc->group_id = $group_id;
      
      try { $gc->save(); }
      catch(Doctrine_Exception $e) {}
    }
    
    $this->getUser()->setFlash('notice',__('The chosen contacts and professionals have been added to the selected groups.'));
    $this->redirect('@contact');
  }
  
  public function postExecute()
  {
    $this->addExtraRequirements();
    $this->getContext()->getConfiguration()->changeTemplatesDir($this);
    return parent::postExecute();
  }
  
  public function executeShow(sfWebRequest $request)
  {
    $this->contact = Doctrine::getTable('Contact')->findWithTickets($request->getParameter('id'));
    $this->forward404Unless($this->contact instanceof Contact);
    $this->form = $this->configuration->getForm($this->contact);
  }
  protected function addExtraRequirements()
  {
    if ( sfConfig::get('app_options_design') == 'tdp' )
    {
      if ( !isset($this->hasFilters) )
        $this->hasFilters = $this->getUser()->getAttribute('contact.filters', $this->configuration->getFilterDefaults(), 'admin_module');
      if ( !isset($this->filters) )
        $this->filters = $this->configuration->getFilterForm($this->getFilters());
      if ( !in_array($this->getActionName(), array('index','search','map','labels','csv')) )
        $this->setTemplate('edit');
    }
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->executeShow($request);
    
    if ( sfConfig::get('app_options_design') != 'tdp' && !$this->getUser()->hasCredential('pr-contact-edit') )
      $this->setTemplate('show');
  }
  
  public function executeNew(sfWebRequest $request)
  {
    parent::executeNew($request);
    $this->object = $this->form->getObject();
    $this->form->getWidget('name')->setOption('default',$request->getParameter('name'));
  }
  public function executeCreate(sfWebRequest $request)
  {
    // hack for the title to be recorded properly
    $params = $request->getParameter('contact');
    if ( !$params['title'] && $autocomplete = $request->getParameter('autocomplete_contact') )
      $params['title'] = $autocomplete['title'];
    $request->setParameter('contact',$params);
    
    parent::executeCreate($request);
    
    if ( $this->form->isValid() && $params['phone_number'] )
    {
      $pn = new ContactPhonenumber();
      $pn->name = $params['phone_type'];
      $pn->number = $params['phone_number'];
      $pn->contact_id = $this->contact->id;
      $pn->save();
    }
  }
  
  public function executeSearchIndexing(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    $table = Doctrine_Core::getTable('Contact');
    $table->getTemplate('Doctrine_Template_Searchable')->getPlugin()
      ->setOption('analyzer', new MySearchAnalyzer());
    $table->batchUpdateIndex($nb = 1500);
    
    $this->getUser()->setFlash('notice',__('%nb% records have been indexed',array('%nb%' => $nb)));
    $this->executeIndex($request);
    $this->setTemplate('index');
  }
  
  public function executeLabels(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/labels.php');
  }
  public function executeDuplicates(sfWebRequest $request)
  {
    self::executeIndex($request);
    
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $q = new Doctrine_RawSql();
    $q->from('Contact c')
      ->leftJoin('(select lower(name) as name, lower(firstname) as firstname, count(*) AS nb from contact group by lower(name), lower(firstname) order by lower(name), lower(firstname)) AS c2 ON c2.firstname ILIKE c.firstname AND c2.name ILIKE c.name')
      ->where('c2.nb > 1')
      ->orderBy('lower(c.name), lower(c.firstname), c.id')
      ->addComponent('c','Contact')
      ->addComponent('c2','Contact');
      $this->pager->setQuery($q);
    
    $this->pager->init();
  }
  public function executeBatchMerge(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/batch-merge.php');
  }
  
  public function executeSearch(sfWebRequest $request)
  {
    self::executeIndex($request);
    
    $table = Doctrine_Core::getTable('Contact');
    
    if ( intval($request->getParameter('s')) > 0 )
    {
      $value = $request->getParameter('s');
      try { $value = liBarcode::decode_ean($value); }
      catch ( sfException $e )
      { $value = intval($value); }
      
      $this->pager->setQuery($table->createQuery('c')->leftJoin('c.MemberCards mc')->andWhere('c.id = ?',$value));
    }
    else
    {
      $search = $this->sanitizeSearch($request->getParameter('s'));
      $transliterate = sfContext::getInstance()->getConfiguration()->transliterate;
      
      $this->pager->setQuery($table->search($search.'*',$this->pager->getQuery())->orWhere('o.name ILIKE ?',$search.'%'));
    }
    
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $this->pager->init();
    
    $this->setTemplate('index');
  }
  public function executeGroupList(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/group-list.php');
  }
  public function executeEmailList(sfWebRequest $request)
  {
    if ( !$request->getParameter('id') )
      $this->forward('contact','index');
    
    $this->group_id = $this->email_id = $request->getParameter('id');
    $q = Doctrine::getTable('Contact')->createQueryByEmailId($this->email_id);
    
    $this->pager = $this->configuration->getPager('Contact');
    $this->pager->setMaxPerPage(15);
    $this->pager->setQuery($q);
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $this->pager->init();
  }
  public function executeIndex(sfWebRequest $request) {
    parent::executeIndex($request);
    if ( !$this->sort[0] )
    {
      $this->sort = array('name','');
      $this->pager->getQuery()->orderby('name');
    }
  }
  public function executeAjax(sfWebRequest $request)
  {
    //$this->getResponse()->setContentType('application/json');
    
    $charset = sfContext::getInstance()->getConfiguration()->charset;
    $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q'));
    
    $q = Doctrine::getTable('Contact')
      ->createQuery('c')
      ->orderBy('c.name, c.firstname')
      ->limit($request->getParameter('limit'));
    if ( $request->getParameter('email') == 'true' )
    $q->andWhere("c.email IS NOT NULL AND email != ?",'');
    $q = Doctrine_Core::getTable('Contact')
      ->search($search.'*',$q);
    $request = $q->execute()->getData();
    
    $contacts = array();
    foreach ( $request as $contact )
      $contacts[$contact->id] = (string) $contact;
    
    return $this->renderText(json_encode($contacts));
  }
  
  public function executeCsv(sfWebRequest $request, $labels = false)
  {
    require(dirname(__FILE__).'/csv.php');
  }
  
  // creates a group from filter criterias
  public function executeGroup(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/group.php');
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

  public function executeEmailing(sfWebRequest $request)
  {
    $this->redirect('email/new');
  }
  
  public function executeGetSpecializedForm(sfWebRequest $request)
  {
    $this->executeEdit($request);
    $this->form->displayOnly($this->field = $request->getParameter('field'));
    $this->setLayout('empty');
  }
  
  public function executeCard(sfWebRequest $request)
  {
    return require(dirname(__FILE__).'/card.php');
  }
  
  public static function sanitizeSearch($search)
  {
    $nb = strlen($search);
    $charset = sfContext::getInstance()->getConfiguration()->charset;
    return strtolower(iconv($charset['db'],$charset['ascii'],substr($search,$nb-1,$nb) == '*' ? substr($search,0,$nb-1) : $search));
  }
}
