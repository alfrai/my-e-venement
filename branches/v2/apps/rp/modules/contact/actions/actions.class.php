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
  public function executeGroupList(sfWebRequest $request)
  {
    if ( !$request->getParameter('id') )
      $this->forward('contact','index');
    
    $this->group_id = $request->getParameter('id');
    
    $this->pager = $this->configuration->getPager('Contact');
    $this->pager->setQuery(
      Doctrine::getTable('Contact')->createQueryByGroupId($this->group_id)
    );
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $this->pager->init();
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
    $this->options = array(
      'ms'        => $request->hasParameter('ms'),
      'nopro'     => $request->hasParameter('nopro'),
      'noheader'  => $request->hasParameter('noheader'),
      'pro_only'  => $criterias['organism_id'] || $criterias['organism_category_id']
                  || $criterias['professional_type_id'],
    );
    
    $q = $this->buildQuery();
    $a = $q->getRootAlias();
    $q->select   ("$a.title, $a.name, $a.firstname, $a.address, $a.postalcode, $a.city, $a.country, $a.npai, $a.email")
      ->addSelect("(SELECT tmp.name FROM ContactPhonenumber tmp WHERE contact_id = $a.id ORDER BY updated_at LIMIT 1) AS phonename")
      ->addSelect("(SELECT tmp2.number FROM ContactPhonenumber tmp2 WHERE contact_id = $a.id ORDER BY updated_at LIMIT 1) AS phonenumber")
      ->addSelect("$a.description");
    if ( !$this->options['nopro'] )
    {
      $q->leftJoin('o.Category oc')
        ->addSelect("oc.name AS organism_category, o.name AS organism_name")
        ->addSelect('p.department AS professional_department, p.contact_number AS professional_number, p.contact_email AS professional_email')
        ->addSelect('pt.name AS professional_type_name, p.name AS professional_name')
        ->addSelect("o.address AS organism_address, o.postalcode AS organism_postalcode, o.city AS organism_city, o.country AS organism_country, o.email AS organism_email, o.url AS organism_url, o.npai AS organism_npai, o.description AS organism_description");
    }
    $q->leftJoin(" p.ProfessionalGroups mp ON mp.group_id = gp.id AND mp.professional_id = p.id")
      ->leftJoin("$a.ContactGroups      mc ON mc.group_id = gc.id AND mc.contact_id     = $a.id")
      ->addSelect("(CASE WHEN mc.information IS NOT NULL THEN mc.information ELSE mp.information END) AS information");
    
    $this->lines = $q->fetchArray();
    
    $this->outstream = 'php://output';
    $this->delimiter = $request->hasParameter('ms') ? ';' : ',';
    $this->enclosure = '"';
    $this->charset   = sfContext::getInstance()->getConfiguration()->charset;
    
    if ( !$request->hasParameter('debug') )
      sfConfig::set('sf_web_debug', false);
    sfConfig::set('sf_escaping_strategy', false);
    sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
    
    if ( !$request->hasParameter('debug') )
    {
      $this->getResponse()->clearHttpHeaders();
      $this->getResponse()->setContentType('text/comma-separated-values');
      $this->getResponse()->addHttpMeta('content-disposition', 'attachment; filename="'.$this->getModuleName().'s-list.csv"',true);
      $this->getResponse()->sendHttpHeaders();
    }
    
    if ( !$request->hasParameter('debug') )
      $this->setLayout(false);
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
}
