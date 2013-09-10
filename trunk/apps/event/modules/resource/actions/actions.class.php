<?php

require_once dirname(__FILE__).'/../lib/resourceGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/resourceGeneratorHelper.class.php';

/**
 * location actions.
 *
 * @package    e-venement
 * @subpackage resource
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class resourceActions extends autoResourceActions
{
  public function executeCalendar(sfWebRequest $request)
  {
    $this->executeEdit($request);
  }
}
