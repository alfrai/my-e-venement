<?php

/**
 * tickets actions.
 *
 * @package    e-venement
 * @subpackage tickets
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ticketsActions extends sfActions
{
  protected function addQueryParts(Doctrine_Query $q, $pro = false)
  {
    $q->andWhere(sprintf('t.professional_id IS %s NULL', $pro ? 'NOT' : ''))
      ->andWhere('(tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL)')
      ->andWhere('tck.duplicating IS NULL AND tck.cancelling IS NULL')
      ->leftJoin('tck.Manifestation m')
      ->leftJoin('m.Event e');
    
    $criterias = $this->form->getValues();

    // dates
    if ( !isset($criterias['dates']) )
      $criterias['dates'] = array();
    if ( !isset($criterias['dates']['from']) )
      $criterias['dates']['from'] = date('Y-m-d',strtotime('1 month ago'));
    if ( !isset($criterias['dates']['to']) )
      $criterias['dates']['to'] = date('Y-m-d',strtotime('tomorrow'));
    $q->andWhere('(tck.printed_at IS NOT NULL AND tck.printed_at >= ? OR tck.integrated_at IS NOT NULL AND tck.integrated_at >= ?)',array($criterias['dates']['from'],$criterias['dates']['from']))
      ->andWhere('(tck.printed_at IS NOT NULL AND tck.printed_at <  ? OR tck.integrated_at IS NOT NULL AND tck.integrated_at <  ?)',array($criterias['dates']['to'],$criterias['dates']['to']));
    
    // workspaces
    if ( isset($criterias['workspaces_list']) && is_array($criterias['workspaces_list']) )
      $q->leftJoin('tck.Gauge g')
        ->andWhereIn('g.workspace_id',$criterias['workspaces_list']);
    
    // metaevents
    if ( isset($criterias['meta_events_list']) && is_array($criterias['meta_events_list']) )
      $q->andWhereIn('e.meta_event_id',$criterias['meta_events_list']);
    
    return $q;
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    if ( $request->hasParameter('criterias') )
    {
      $this->criterias = $request->getParameter('criterias');
      $this->getUser()->setAttribute('stats.criterias',$this->criterias,'admin_module');
      $this->redirect($this->getContext()->getModuleName().'/index');
    }

    $this->form = new StatsCriteriasForm;
    //$this->form->addWithContactCriteria();
    $this->form->addEventCriterias();
    if ( is_array($this->getUser()->getAttribute('stats.criterias',array(),'admin_module')) )
    {
      $criterias = $this->getUser()->getAttribute('stats.criterias',array('dates' => array(
        'from' => date('Y-m-d',strtotime('1 month ago')),
        'to'   => date('Y-m-d',strtotime('tomorrow')),
      )),'admin_module');
      
      if ( !isset($criterias['dates']) )
        $criterias['dates'] = array();
      if ( !isset($criterias['dates']['from']) || isset($criterias['dates']['from']) && !$criterias['dates']['from']['day'] )
        $criterias['dates']['from'] = date('Y-m-d',strtotime('1 month ago'));
      if ( !isset($criterias['dates']['to']) || isset($criterias['dates']['to']) && !$criterias['dates']['to']['day'] )
        $criterias['dates']['to'] = date('Y-m-d',strtotime('tomorrow'));
      
      $this->form->bind($criterias);
    }
    $this->criterias = $this->form->getValues();
    
    $this->professionals = $this->contacts = array('nb' => 0, 'tickets' => 0, 'events' => 0,);
    
    // PERSO
    
    $q = Doctrine_Query::create()->from('Contact c')
      ->leftJoin('c.Transactions t')
      ->leftJoin('t.Tickets tck')
      ->select('c.id, count(DISTINCT e.id) AS nb_events')
      ->groupBy('c.id');
    $contacts = $this->addQueryParts($q)->execute();
    
    // number of contacts & events/contacts
    $this->contacts['nb'] = $contacts->count();
    $this->contacts['events'] = 0;
    foreach ( $contacts as $contact )
      $this->contacts['events'] += $contact->nb_events;
    
    // nb of contacts' tickets
    $q = Doctrine_Query::create()->from('Ticket tck')
      ->leftJoin('tck.Transaction t')
      ->select('tck.id');
    $this->contacts['tickets'] = $this->addQueryParts($q)->count();
    
    // PRO
    
    // number of contacts & nb of events/contacts
    $q = Doctrine_Query::create()->from('Professional p')
      ->leftJoin('p.Transactions t')
      ->leftJoin('t.Tickets tck')
      ->select('p.id, count(DISTINCT e.id) AS nb_events')
      ->groupBy('p.id');
    $professionals = $this->addQueryParts($q,true)->execute();
    
    $this->professionals['nb'] = $professionals->count();
    $this->professionals['events'] = 0;
    foreach ( $professionals as $professional )
      $this->professionals['events'] += $professional->nb_events;
    
    $q = Doctrine_Query::create()->from('Ticket tck')
      ->leftJoin('tck.Transaction t');
    $this->professionals['tickets'] = $this->addQueryParts($q,true)->count();
  }
}
