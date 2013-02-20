<?php

require_once dirname(__FILE__).'/../lib/priceGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/priceGeneratorHelper.class.php';

/**
 * price actions.
 *
 * @package    e-venement
 * @subpackage price
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class priceActions extends autoPriceActions
{
  public function executeIndex(sfWebRequest $request)
  {
    parent::executeIndex($request);
    if ( !$this->sort[0] )
    {
      $this->sort = array('name','');
      $a = $this->pager->getQuery()->getRootAlias();
      $this->pager->getQuery()
        ->select('*')
        ->orderby("$a.hide, $a.name");
    }
  }
}
