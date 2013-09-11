<?php

require_once dirname(__FILE__).'/../lib/waitingGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/waitingGeneratorHelper.class.php';

/**
 * waiting actions.
 *
 * @package    e-venement
 * @subpackage waiting
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class waitingActions extends autoWaitingActions
{
  public function executeBatchConfirm(sfWebRequest $request)
  {
    $this->doConfirm($request->getParameter('ids'));
    $this->redirect('waiting/index');
  }
  public function executeConfirm(sfWebRequest $request)
  {
    $id = $request->getParameter('id');
    $this->doConfirm(array($id));
    
    $this->redirect('waiting/index');
  }
  public function doConfirm(array $ids)
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

