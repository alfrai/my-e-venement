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
  public function preExecute()
  {
    $this->dispatcher->notify(new sfEvent($this, 'pub.pre_execute', array('configuration' => $this->configuration)));
    parent::preExecute();
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    if ( $this->getPager()->getQuery()->count() == 1 )
    {
      $manifestation = $this->getPager()->getQuery()->select('m.id')->fetchOne();
      $this->redirect('manifestation/edit?id='.$manifestation->id);
    }
  }
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
    $q = Doctrine::getTable('Gauge')->createQuery('g')
      ->addSelect('gtck.*, m.*, mpm.*, mp.*, tck.*, e.*, l.*, ws.*, sp.*, op.*')
      ->andWhere('g.online = ?', true)
      
      ->leftJoin('g.Tickets gtck WITH gtck.price_id IS NULL AND gtck.seat_id IS NOT NULL AND gtck.transaction_id = ?', $this->getUser()->getTransaction()->id)
      ->leftJoin('g.Manifestation m')
      ->andWhere('(m.happens_at > NOW() OR ?)',sfContext::getInstance()->getConfiguration()->getEnvironment() == 'dev')
      ->andWhere('m.id = ?',$request->getParameter('id'))
      ->andWhere('m.reservation_confirmed = ?',true)
      
//      ->leftJoin('g.Workspace ws')
      ->leftJoin('m.Location l')
      ->leftJoin('l.SeatedPlans sp')
      ->leftJoin('sp.Workspaces spws')
      ->leftJoin('ws.Users wu')
      ->leftJoin('m.Event e')
      ->leftJoin('e.MetaEvent me')
      ->leftJoin('g.PriceGauges gpg')
      ->leftJoin('gpg.Price gp')
      ->leftJoin('m.PriceManifestations mpm')
      ->leftJoin('mpm.Price mp')
      ->leftJoin('mp.Tickets tck WITH tck.gauge_id = g.id AND tck.transaction_id = ?', $this->getUser()->getTransaction()->id)
      
      ->leftJoin('gp.Users gpu')
      ->leftJoin('gp.Workspaces gpw')
      ->leftJoin('mp.Users mpu')
      ->leftJoin('mp.Workspaces mpw')
      
      ->andWhere('gpu.id = ? OR mpu.id = ?', array($this->getUser()->getId(), $this->getUser()->getId()))
      ->andWhere('wu.id = mpu.id OR wu.id = gpu.id')
      ->andWhere('mpw.id = ws.id OR gpw.id = ws.id')
      ->andWhere('gpw.id = g.workspace_id')
      
      ->andWhereIn('ws.id',array_keys($this->getUser()->getWorkspacesCredentials()))
      ->andWhereIn('me.id',array_keys($this->getUser()->getMetaEventsCredentials()))
      
      ->orderBy('g.group_name, ws.name, mp.name, gp.name')
    ;
    $this->gauges = $q->execute();
    
    if ( !$this->gauges || $this->gauges && $this->gauges->count() <= 0 )
    {
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('error',__('Date unavailable, try an other one.'));
      $this->redirect('event/index');
    }
    
    $this->manifestation = $this->gauges[0]->Manifestation;
    $this->form = new PricesPublicForm;
    
    $this->mcp = $this->getAvailableMCPrices();
    
    if ( strtotime('now + '.sfConfig::get('app_tickets_close_before','36 hours')) > strtotime($this->manifestation->happens_at) )
      return 'Closed';
  }
  
  protected function getAvailableMCPrices()
  {
    $mcp = array();
    try {

    $contact = $this->getUser()->getContact();
    if ( $contact->MemberCards->count() == 0 )
      return $mcp;
    
    // get back available prices
    foreach ( $contact->MemberCards as $mc )
    foreach ( $mc->MemberCardPrices as $price )
    {
      if ( !isset($mcp[$price->price_id]['']) )
        $mcp[$price->price_id][''] = 0;
      
      if ( isset($mcp[$price->price_id][is_null($price->event_id) ? '' : $price->event_id]) )
        $mcp[$price->price_id][is_null($price->event_id) ? '' : $price->event_id]++;
      else
        $mcp[$price->price_id][is_null($price->event_id) ? '' : $price->event_id] = 1;
    }
    
    // get back already booked tickets
    $tickets_to_count = Doctrine_Query::create()->from('Ticket tck')
      ->andWhere('tck.printed_at IS NULL')
      ->andWhere('tck.member_card_id IS NOT NULL')
      ->leftJoin('tck.Manifestation m')
      ->leftJoin('tck.Price p')
      ->andWhere('p.member_card_linked = ?',true)
      ->leftJoin('tck.Transaction t')
      ->andWhere('t.contact_id = ?',$this->getUser()->getContact()->id)
      ->leftJoin('t.Order o')
      ->andWhere('o.id IS NOT NULL')
      ->execute();
    foreach ( $tickets_to_count as $ticket )
    {
      if ( isset($mcp[$ticket->price_id][$ticket->Manifestation->event_id]) )
        $mcp[$ticket->price_id][$ticket->Manifestation->event_id]--;
      else
        $mcp[$ticket->price_id]['']--;
    }
    
    return $mcp;
    
    }
    catch ( liEvenementException $e )
    { return $mcp; }
  }
}
