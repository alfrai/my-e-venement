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
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $ilike = '%'.$request->getParameter('q').'%';
    
    $this->getResponse()->setContentType('application/json');
    $q = Doctrine::getTable('Professional')->createQuery();
    $a = $q->getRootAlias();
    $q->where('c.name ILIKE ? OR c.firstname ILIKE ? OR o.name ILIKE ?',array($ilike,$ilike,$ilike))
      ->limit($request->getParameter('limit'));
    $request = $q->execute()->getData();
    
    //echo $q->getSqlQuery();
    
    $professionals = array();
    foreach ( $request as $professional )
      $professionals[$professional->id] = $professional->getFullName();
    
    return $this->renderText(json_encode($professionals));
  }
}
