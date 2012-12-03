<?php

require_once dirname(__FILE__).'/../lib/manifestationGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/manifestationGeneratorHelper.class.php';

/**
 * manifestation actions.
 *
 * @package    symfony
 * @subpackage manifestation
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class manifestationActions extends autoManifestationActions
{
  public function executeBatchDelete(sfWebRequest $request)
  {
    $this->redirect('manifestation/index');
  }
  public function executeCreate(sfWebRequest $request)
  {
    $this->redirect('manifestation/index');
  }
  public function executeNew(sfWebRequest $request)
  {
    $this->redirect('manifestation/index');
  }
  public function executeUpdate(sfWebRequest $request)
  {
    $manif = $request->getParameter('manifestation');
    $this->redirect('manifestation/show?id='.$manif['id']);
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->manifestation = $this->getRoute()->getObject();
    $this->redirect('manifestation/show?id='.$this->manifestation->id);
  }
  public function executeShow(sfWebRequest $request)
  {
    $this->gauges = Doctrine::getTable('Gauge')->createQuery('g')
      ->addSelect('m.*, pm.*, p.*, tck.*, e.*')
      ->leftJoin('g.Manifestation m')
      ->leftJoin('ws.Users wu')
      ->leftJoin('m.Event e')
      ->leftJoin('m.PriceManifestations pm')
      ->leftJoin('pm.Price p')
      ->leftJoin('p.Users pu')
      ->leftJoin('p.Workspaces pw')
      ->leftJoin('p.Tickets tck ON tck.gauge_id = g.id AND tck.price_id = p.id AND tck.transaction_id = ?',$this->getUser()->getAttribute('transaction_id',0))
      ->andWhere('pu.id = ?',$this->getUser()->getId())
      ->andWhere('wu.id = pu.id')
      ->andWhere('pw.id = ws.id')
      ->andWhere('pw.id = g.workspace_id')
      ->andWhere('m.id = ?',$request->getParameter('id'))
      ->execute();
    
    //if ( $this->getUser()->hasAttribute('transaction_id')
    
    if ( !$this->gauges || $this->gauges && $this->gauges->count() <= 0 )
    {
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('error',__('Date unavailable, try an other one.'));
      $this->redirect('event/index');
    }
    
    $this->manifestation = $this->gauges[0]->Manifestation;
    $this->form = new PricesPublicForm;
  }
}


