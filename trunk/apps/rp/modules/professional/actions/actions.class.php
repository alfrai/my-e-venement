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
    
    $charset = sfContext::getInstance()->getConfiguration()->charset;
    $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q'));
    
    $ids = array();

    $q = Doctrine_Core::getTable('Contact')
      ->search($search.'*',Doctrine_Query::create()
      ->select('c.id, p.id AS pid')
      ->from('Contact c')
      ->leftJoin('c.Professionals p')
      ->limit($request->getParameter('limit')*3)
      ->andWhere($request->getParameter('email') == 'true' ? "contact_email IS NOT NULL AND contact_email != ?" : '','')
    );
    $cids = $q->fetchArray();
    foreach ( $cids as $cid )
      $ids[$cid['pid']] = $cid['pid'];
    
    $q = Doctrine_Core::getTable('Organism')
      ->search($search.'*',Doctrine_Query::create()
      ->select('c.id, p.id AS pid')
      ->from('Organism c')
      ->leftJoin('c.Professionals p')
      ->limit($request->getParameter('limit')*3)
      ->andWhere($request->getParameter('email') == 'true' ? "contact_email IS NOT NULL AND contact_email != ?" : '','')
    );
    $oids = $q->fetchArray();
    foreach ( $oids as $cid )
      $ids[$cid['pid']] = $cid['pid'];
    
    unset($ids['']);
    
    $professionals = array();
    
    if ( count($ids) == 0 )
      return $this->renderText(json_encode($professionals));;
    
    $q = Doctrine::getTable('Professional')->createQuery();
    $a = $q->getRootAlias();
    $q->whereIn("$a.id",$ids);
    $request = $q->execute()->getData();
    
    foreach ( $request as $professional )
      $professionals[$professional->id] = $professional->getFullName();
    
    return $this->renderText(json_encode($professionals));
  }
}
