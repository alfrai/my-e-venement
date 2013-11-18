<?php

require_once dirname(__FILE__).'/../lib/pictureGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/pictureGeneratorHelper.class.php';

/**
 * picture actions.
 *
 * @package    e-venement
 * @subpackage picture
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class pictureActions extends autoPictureActions
{
  public function executeDisplay(sfWebRequest $request)
  {
    $this->executeShow($request);
    $this->getResponse()->addHttpMeta('Content-Type',$this->picture->type);
    $this->getResponse()->addHttpMeta('Content-Disposition','inline; filename='.$this->picture->name);
    $this->getResponse()->addHttpMeta('Cache-Control',$cache = 'public, no-store');
    $this->getResponse()->addHttpMeta('Pragma',$cache);
  }
}
