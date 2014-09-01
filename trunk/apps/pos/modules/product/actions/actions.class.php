<?php

require_once dirname(__FILE__).'/../lib/productGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/productGeneratorHelper.class.php';

/**
 * product actions.
 *
 * @package    e-venement
 * @subpackage product
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class productActions extends autoProductActions
{
  public function executeDelPicture(sfWebRequest $request)
  {
    Doctrine::getTable('Product')->find($request->getParameter('id', 0))->Picture->delete();
    return sfView::NONE;
  }
}
