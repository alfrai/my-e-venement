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
    if ( strlen($request->getParameter('q')) > 2 )
    {
      $charset = sfConfig::get('software_internals_charset');
      $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q'));
      
      $q = Doctrine::getTable('Postalcode')
        ->createQuery()
        ->orderBy('city')
        ->andWhere('postalcode LIKE ?',$request->getParameter('q').'%');
      $postalcodes = $q->execute();
      
      $arr = array();
      foreach ( $postalcodes as $cp )
        $arr[$cp->city.' %%'.$cp->postalcode.'%%'] = (string)$cp;
      return $this->renderText(json_encode($arr));
    }
    
    // empty
    return $this->renderText(json_encode(array()));
  }


}
