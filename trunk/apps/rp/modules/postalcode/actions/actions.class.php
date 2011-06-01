<?php

require_once dirname(__FILE__).'/../lib/postalcodeGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/postalcodeGeneratorHelper.class.php';

/**
 * postalcode actions.
 *
 * @package    e-venement
 * @subpackage postalcode
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postalcodeActions extends autoPostalcodeActions
{
  public function executeAjax(sfWebRequest $request)
  {
    if ( strlen($request->getParameter('q')) > 3 )
    {
      $charset = sfContext::getInstance()->getConfiguration()->charset;
      $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q'));
      
      $q = Doctrine::getTable('Postalcode')
        ->createQuery()
        ->orderBy('city')
        ->andWhere('postalcode LIKE ?',$request->getParameter('q').'%');
      $postalcodes = $q->execute();
      
      $organisms = array();
      foreach ( $postalcodes as $cp )
        $organisms[$cp->city] = $cp->postalcode.' '.$cp->city;
      
      return $this->renderText(json_encode($organisms));
    }
    
    // empty
    return $this->renderText(json_encode(array()));
  }


}
