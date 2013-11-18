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
      ->leftJoin('p.Tickets tck ON tck.gauge_id = g.id AND tck.price_id = p.id AND tck.transaction_id = ?',$this->getUser()->getTransaction()->id)
      ->andWhere('pu.id = ?',$this->getUser()->getId())
      ->andWhere('wu.id = pu.id')
      ->andWhere('pw.id = ws.id')
      ->andWhere('pw.id = g.workspace_id')
      ->andWhere('m.id = ?',$request->getParameter('id'))
      ->andWhere('m.happens_at > NOW() OR ?',sfContext::getInstance()->getConfiguration()->getEnvironment() == 'dev')
      ->andWhere('g.online = TRUE')
      ->execute();
    
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
      ->andWhere('tck.printed = ?',false)
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
