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
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    $ids = $request->getParameter('ids');
    $groups = $request->getParameter('groups');
    
    foreach ( $ids as $contact_id )
    foreach ( $groups as $group_id )
    {
      $gc = new GroupContact();
      $gc->contact_id = $contact_id;
      $gc->group_id = $group_id;
      
      try { $gc->save(); }
      catch(Doctrine_Exception $e) {}
    }
    
    $this->getUser()->setFlash('notice',__('The chosen contacts have been added to the selected groups.'));
    $this->redirect('@contact');
  }
  
  public function executeShow(sfWebRequest $request)
  {
    $this->contact = Doctrine::getTable('Contact')->findWithTickets($request->getParameter('id'));
    $this->forward404Unless($this->contact);
    $this->form = $this->configuration->getForm($this->contact);
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->executeShow($request);
    
    if ( !$this->getUser()->hasCredential('pr-contact-edit') )
      $this->setTemplate('show');
  }
  
  public function executeCreate(sfWebRequest $request)
  {
    parent::executeCreate($request);
    
    $params = $request->getParameter('contact');
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
    return require(dirname(__FILE__).'/labels.php');
  }
  public function executeDuplicates(sfWebRequest $request)
  {
    self::executeIndex($request);
    
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    /*$q = Doctrine_Core::getTable('Contact')
      ->createQuery('c')
      ->
      ->andWhere('(SELECT count(*) FROM Contact c2 WHERE c2.id != c.id AND c.name ILIKE c2.name AND c2.firstname ILIKE c.firstname) > 0')
      ->orderBy('name,firstname');
    */
    $q = new Doctrine_RawSql();
    $q->from('Contact c')
      ->leftJoin('(select min(id) AS id, count(*) AS nb from contact group by lower(name), lower(firstname) order by lower(name), lower(firstname)) AS c2 on c2.id = c.id')
      ->where('c2.nb > 1')
      ->addComponent('c','Contact')
      ->addComponent('c2','Contact');
      $this->pager->setQuery($q);
    
    $this->pager->init();
    $this->setTemplate('index');
  }
  public function executeSearch(sfWebRequest $request)
  {
    self::executeIndex($request);
    
    $table = Doctrine_Core::getTable('Contact');
    
    if ( intval($request->getParameter('s')).'' === $request->getParameter('s'))
    {
      $value = intval($request->getParameter('s'));
      try { $value = liBarcode::decode_ean($value); }
      catch ( sfException $e )
      { $value = intval($value); }
      
      $this->pager->setQuery($table->createQuery('c')->andWhere('c.id = ?',$value));
    }
    else
    {
      $search = $this->sanitizeSearch($request->getParameter('s'));
      $transliterate = sfContext::getInstance()->getConfiguration()->transliterate;
      
      $this->pager->setQuery($table->search($search.'*',$this->pager->getQuery()));
    }
    
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $this->pager->init();
    
    $this->setTemplate('index');
  }
  public function executeGroupList(sfWebRequest $request)
  {
    if ( !$request->getParameter('id') )
      $this->forward('contact','index');
    
    $this->group_id = $request->getParameter('id');
    
    $this->pager = $this->configuration->getPager('Contact');
    $this->pager->setMaxPerPage(15);
    $this->pager->setQuery(
      Doctrine::getTable('Contact')->createQueryByGroupId($this->group_id)
    );
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $this->pager->init();
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
    $q = $this->buildQuery();
    $a = $q->getRootAlias();
    $q->select   ("$a.id, p.id AS professional_id");
    $records = $q->fetchArray();
    
    if ( $q->count() > 0 )
    {
      $group = new Group();
      if ( $this->getUser() instanceof sfGuardSecurityUser )
        $group->sf_guard_user_id = $this->getUser()->id;
      $group->name = __('Search group').' - '.date('Y-m-d H:i:s');
      $group->sf_guard_user_id = $this->getUser()->getId();
      $group->save();
      
      foreach ( $records as $record )
      {
        // contact
        if ( !$record['professional_id'] )
        {
          $member = new GroupContact();
          $member->contact_id = $record['id'];
        }
        else
        {
          $member = new GroupProfessional();
          $member->professional_id = $record['professional_id'];
        }
        
        $member->group_id   = $group->id;
        $member->save();
      }
    }
    
    $this->redirect(url_for('group/show?id='.$group->id));
    return sfView::NONE;
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
    $params = $request->getParameter('member_card');
    $params['expire_at'] = date('Y-m-d H:i:s',strtotime(sfConfig::get('app_cards_expiration_delay'),strtotime('now')));
    
    if ( !$request->hasParameter('id') )
      $request->setParameter('id',$params['contact_id']);
    $this->executeShow($request);
    
    $this->card = new MemberCardForm();
    $this->card->bind($params);
    
    if ( $this->card->isValid() )
    {
      if ( $request->hasParameter('duplicate') )
      {
        $q = Doctrine::getTable('MemberCard')->createQuery('mc')
          ->andWhere('mc.contact_id = ?',$params['contact_id'])
          ->andWhere('mc.expire_at > NOW()');
        $card = $q->fetchOne();
        
        if ( !$card )
          return 'Params';
        
        // some kind of a hack
        $this->card = $card;
        $this->card->updated_at = NULL;
        $this->card->name = $params['name'];
      }
      $this->card->save();
      $this->setLayout('nude');
      return 'Success';
    }
    else
    {
      return 'Params';
    }
  }
  
  public static function sanitizeSearch($search)
  {
    $nb = strlen($search);
    $charset = sfContext::getInstance()->getConfiguration()->charset;
    return strtolower(iconv($charset['db'],$charset['ascii'],substr($search,$nb-1,$nb) == '*' ? substr($search,0,$nb-1) : $search));
  }
}
