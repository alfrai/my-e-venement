<?php

require_once dirname(__FILE__).'/../lib/authenticationGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/authenticationGeneratorHelper.class.php';

/**
 * authentication actions.
 *
 * @package    e-venement
 * @subpackage authentication
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class authenticationActions extends autoAuthenticationActions
{
  public function executeIndex(sfWebRequest $request)
  {
    parent::executeIndex($request);
    if ( !$this->sort[0] )
    {
      $this->sort = array('created_at','');
      $q = $this->pager->getQuery()
        ->orderby("created_at DESC");
    }
  }
  
  public function executeUpdate(sfWebRequest $request)
  {
    $this->getUser()->setFlash('notice','Function unavailable for security reasons');
    $this->redirect('authentication');
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->getUser()->setFlash('notice','Function unavailable for security reasons');
    $this->redirect('authentication');
  }
  public function executeNew(sfWebRequest $request)
  {
    $this->getUser()->setFlash('notice','Function unavailable for security reasons');
    $this->redirect('authentication');
  }
  public function executeCreate(sfWebRequest $request)
  {
    $this->getUser()->setFlash('notice','Function unavailable for security reasons');
    $this->redirect('authentication');
  }
  public function executeDelete(sfWebRequest $request)
  {
    $this->getUser()->setFlash('notice','Function unavailable for security reasons');
    $this->redirect('authentication');
  }
  public function executeBatch(sfWebRequest $request)
  {
    $this->getUser()->setFlash('notice','Function unavailable for security reasons');
    $this->redirect('authentication');
  }
  public function executeBatchDelete(sfWebRequest $request)
  {
    $this->getUser()->setFlash('notice','Function unavailable for security reasons');
    $this->redirect('authentication');
  }
}
