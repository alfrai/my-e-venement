<?php

require_once dirname(__FILE__).'/../lib/manifestationGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/manifestationGeneratorHelper.class.php';

/**
 * manifestation actions.
 *
 * @package    e-venement
 * @subpackage manifestation
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class manifestationActions extends autoManifestationActions
{
  public function executeAjax(sfWebRequest $request)
  {
    $charset = sfContext::getInstance()->getConfiguration()->charset;
    $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q'));
    
    $q = Doctrine::getTable('Manifestation')
      ->createQuery()
      ->orderBy('date')
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
}
