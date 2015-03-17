<?php

/**
 * filter module configuration.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: configuration.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class filterGeneratorConfiguration extends BaseFilterGeneratorConfiguration
{
  public function getFilterDefaults()
  {
    return sfContext::hasInstance()
      ? array('sf_guard_user_id' => sfContext::getInstance()->getUser()->getId())
      : array();
  }
}
