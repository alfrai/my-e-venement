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
  public function preExecute()
  {
    $this->dispatcher->notify(new sfEvent($this, 'pub.pre_execute', array('configuration' => $this->configuration)));
    parent::preExecute();
  }
  public function executeDisplay(sfWebRequest $request)
  {
    $this->executeShow($request);
    $this->getResponse()->addHttpMeta('Content-Type',$this->picture->type);
    $this->getResponse()->addHttpMeta('Content-Disposition','inline; filename='.$this->picture->name);
    $this->getResponse()->addHttpMeta('Cache-Control',$cache = 'max-age='.(60*60*48)); // caching data for 48h
    $this->getResponse()->addHttpMeta('Pragma',$cache);
    $this->getResponse()->addHttpMeta('Expires',date(DATE_W3C,time()+$cache)); // caching data for 48h
    
    if ( $this->picture->content_encoding )
      $this->getResponse()->addHttpMeta('Content-Encoding', $this->picture->content_encoding);
  }
  
  public function executeRaw(sfWebRequest $request)
  {
    $this->executeShow($request);
    $this->getResponse()->addHttpMeta('Content-Type',$this->picture->type);
    $this->getResponse()->addHttpMeta('Content-Disposition', 'attachment; filename='.$this->picture->name);
  }
  
  public function executeEdit(sfWebRequest $request)
  { throw new liOnlineSaleException('Access denied detected on '.$this->getActionName()); }
  public function executeUpdate(sfWebRequest $request)
  { throw new liOnlineSaleException('Access denied detected on '.$this->getActionName()); }
  public function executeNew(sfWebRequest $request)
  { throw new liOnlineSaleException('Access denied detected on '.$this->getActionName()); }
  public function executeCreate(sfWebRequest $request)
  { throw new liOnlineSaleException('Access denied detected on '.$this->getActionName()); }
  public function executeDelete(sfWebRequest $request)
  { throw new liOnlineSaleException('Access denied detected on '.$this->getActionName()); }
  public function executeIndex(sfWebRequest $request)
  { throw new liOnlineSaleException('Access denied detected on '.$this->getActionName()); }
  public function executeBatchDelete(sfWebRequest $request)
  { throw new liOnlineSaleException('Access denied detected on '.$this->getActionName()); }
}
