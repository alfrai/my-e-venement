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
  protected static function addQueryParts(Doctrine_Query $q, $pro = false)
  {
    $q->andWhere(sprintf('t.professional_id IS %s NULL', $pro ? 'NOT' : ''))
      ->andWhere('tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL')
      ->andWhere('tck.duplicating IS NULL AND tck.cancelling IS NULL')
      ->leftJoin('tck.Manifestation m')
      ->leftJoin('m.Event e');
    return $q;
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = new StatsCriteriasForm();
    $this->form->addWithContactCriteria();
    $this->form->addEventCriterias();
    if ( is_array($this->getUser()->getAttribute('stats.criterias',array(),'admin_module')) )
      $this->form->bind($this->getUser()->getAttribute('stats.criterias',array(),'admin_module'));
    
    $this->professionals = $this->contacts = array();
    
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
    $this->contacts['tickets'] = $this->addQueryParts($q)->execute()->count();
    
    // PRO
    
    $q = Doctrine_Query::create()->from('Professional p')
      ->leftJoin('p.Transactions t')
      ->leftJoin('t.Tickets tck')
      ->select('p.id, count(DISTINCT e.id) AS nb_events')
      ->groupBy('p.id');
    $professionals = $this->addQueryParts($q,true)->execute();
    
    // number of contacts & nb of events/contacts
    $this->professionals['nb'] = $professionals->count();
    $this->professionals['events'] = 0;
    foreach ( $professionals as $professional )
      $this->professionals['events'] += $professional->nb_events;
    
    $q = Doctrine_Query::create()->from('Ticket tck')
      ->leftJoin('tck.Transaction t')
      ->select('tck.id');
    $this->professionals['tickets'] = $this->addQueryParts($q,true)->execute()->count();
  }
}
