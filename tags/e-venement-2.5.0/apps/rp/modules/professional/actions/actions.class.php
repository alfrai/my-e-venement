<?php

require_once dirname(__FILE__).'/../lib/professionalGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/professionalGeneratorHelper.class.php';

/**
 * professional actions.
 *
 * @package    e-venement
 * @subpackage professional
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class professionalActions extends autoProfessionalActions
{
  public function executeAjax(sfWebRequest $request)
  {
    $this->getResponse()->setContentType('application/json');
    
    $charset = sfConfig::get('software_internals_charset');
    $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q'));
    
    $ids = array();

    $q = Doctrine_Core::getTable('Contact')
      ->search($search.'*',Doctrine_Query::create()
      ->select('c.id, p.id AS pid')
      ->from('Contact c')
      ->leftJoin('c.Professionals p')
      ->andWhere("p.contact_email IS NOT NULL AND p.contact_email != ?".($request->getParameter('email') == 'true' ? '' : ' OR TRUE'),'')
      ->limit($request->getParameter('limit')*3)
      ->andWhere($request->getParameter('email') == 'true' ? "contact_email IS NOT NULL AND contact_email != ''" : 'TRUE')
    );
    $cids = $q->fetchArray();
    $contact_ids = array();
    foreach ( $cids as $cid )
      $contact_ids[$cid['id']] = $cid['id'];
    
    $q = Doctrine_Core::getTable('Organism')
      ->search($search.'*',Doctrine_Query::create()
      ->select('c.id, p.id AS pid')
      ->from('Organism c')
      ->leftJoin('c.Professionals p')
      ->limit($request->getParameter('limit')*3)
      ->andWhere($request->getParameter('email') == 'true' ? "contact_email IS NOT NULL AND contact_email != ''" : 'TRUE')
    );
    $oids = $q->fetchArray();
    $organism_ids = array();
    foreach ( $oids as $oid )
      $organism_ids[$oid['id']] = $oid['id'];
    
    unset($ids['']);
    
    $professionals = array();
    
    if ( count($organism_ids) + count($contact_ids) == 0 )
      return $this->renderText(json_encode($professionals));;
    
    $q = Doctrine::getTable('Professional')->createQuery();
    $a = $q->getRootAlias();
    $q->whereIn("$a.organism_id",$organism_ids)
      ->orWhereIn("$a.contact_id",$contact_ids);
    $request = $q->execute()->getData();
    
    foreach ( $request as $professional )
      $professionals[$professional->id] = $professional->getFullName();
    
    return $this->renderText(json_encode($professionals));
  }
}
