<?php

require_once dirname(__FILE__).'/../lib/pendingGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/pendingGeneratorHelper.class.php';

/**
 * pending actions.
 *
 * @package    e-venement
 * @subpackage pending
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class pendingActions extends autoPendingActions
{
  public function executeBatchConfirm(sfWebRequest $request)
  {
    $this->doConfirm($request->getParameter('ids'));
    $this->redirect('pending/index');
  }
  protected function doConfirm(array $ids)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    
    try
    {
      $this->manifestations = Doctrine_Query::create()->from('Manifestation m')->andWhereIn('m.id',$ids)->execute();
      
      foreach ( $this->manifestations as $manif )
      {
        $manif->reservation_confirmed = true;
        $manif->save();
      }
      $this->getUser()->setFlash('success', __('%%nb%% manifestation(s) confirmed', array('%%nb%%' => $this->manifestations->count())));
    }
    catch ( Doctrine_Connection_Exception $e )
    {
      $this->getUser()->setFlash('error', __('Error confirming the manifestation'));
    }
  }
  
  public function executeCalendar(sfWebRequest $request)
  {
    $this->redirect('calendar/index?only_pending=true');
  }
  
  public function executeEdit(sfWebRequest $request)
  {
    $this->redirect('manifestation/edit?id='.$request->getParameter('id'));
  }
  public function executeUpdate(sfWebRequest $request)
  {
    $this->executeEdit($request);
  }
  public function executeShow(sfWebRequest $request)
  {
    $this->redirect('manifestation/show?id='.$request->getParameter('id'));
  }
  public function executeNew(sfWebRequest $request)
  {
    $this->redirect('manifestation/new');
  }
  public function executeCreate(sfWebRequest $request)
  {
    $this->executeNew($request);
  }
}

