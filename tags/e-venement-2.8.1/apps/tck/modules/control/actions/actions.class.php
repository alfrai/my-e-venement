<?php

require_once dirname(__FILE__).'/../lib/controlGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/controlGeneratorHelper.class.php';

/**
 * control actions.
 *
 * @package    e-venement
 * @subpackage control
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class controlActions extends autoControlActions
{
  public function executeIndex(sfWebRequest $request)
  {
    parent::executeIndex($request);
    if ( $request->hasParameter('light') )
      $this->setLayout('nude');
  }
}
