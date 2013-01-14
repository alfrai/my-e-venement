<?php

/**
 * ledger actions.
 *
 * @package    e-venement
 * @subpackage ledger
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ledgerActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->redirect('ledger/cash');
  }
  
  protected function formatCriterias(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    $this->form = new LedgerCriteriasForm();
    $criterias = $request->getParameter($this->form->getName());

    // Hack for form validation
    if ( isset($criterias['users']) && $criterias['users'][0] === '' && count($criterias['users']) == 1 )
      unset($criterias['users']);
    if ( isset($criterias['workspaces']) && $criterias['workspaces'][0] === '' && count($criterias['workspaces']) == 1 )
      unset($criterias['workspaces']);
    
    $this->form->bind($criterias, $request->getFiles($this->form->getName()));
    if ( !$this->form->isValid() )
    {
      $this->getUser()->setFlash('error',__('Submitted values are invalid'));
    }
    
    $dates = array(
      $criterias['dates']['from']['day']
        ? strtotime($criterias['dates']['from']['year'].'-'.$criterias['dates']['from']['month'].'-'.$criterias['dates']['from']['day'])
        : strtotime(sfConfig::has('app_ledger_date_begin') ? sfConfig::get('app_ledger_date_begin').' 0:00' : '1 week ago 0:00'),
      $criterias['dates']['to']['day']
        ? strtotime($criterias['dates']['to']['year'].'-'.$criterias['dates']['to']['month'].'-'.$criterias['dates']['to']['day'])
        : strtotime(sfConfig::has('app_ledger_date_end') ? sfConfig::get('app_ledger_date_end').' 0:00' : 'tomorrow 0:00'),
    );
    
    if ( $dates[0] > $dates[1] )
    {
      $buf = $dates[1];
      $dates[1] = $dates[0];
      $dates[0] = $buf;
    }
    $criterias['dates'] = $dates;
    
    // get all selected users
    $this->users = false;
    if ( isset($criterias['users']) && is_array($criterias['users']) && $criterias['users'][0] )
    {
      $q = Doctrine::getTable('sfGuardUser')->createQuery('u')
        ->andWhereIn('u.id',$criterias['users']);
      $this->users = $q->execute();
    }
    
    return $criterias;
  }
  
  public function executeSales(sfWebRequest $request)
  {
    // because loading this page with a lot of data is really long
    set_time_limit(240);
    ini_set('memory_limit','512M');
    
    $this->options = $criterias = $this->formatCriterias($request);
    $dates = $criterias['dates'];
    
    // BE CAREFUL : ALWAY CHECK Manifestation::getTicketsInfos() FOR CRITERIAS APPLYIANCE FOR BIG LEDGERS
    
    $q = Doctrine::getTable('Event')->createQuery('e')
      ->leftJoin('e.Manifestations m')
      ->leftJoin('m.Location l')
      ->leftJoin('m.Tickets tck')
      ->leftJoin('tck.User u')
      ->leftJoin('tck.Duplicated d')
      ->andWhere('d.id IS NULL') // to count only originals tickets, not duplicates
      ->leftJoin('tck.Transaction t')
      ->leftJoin('tck.Gauge g')
      ->leftJoin('t.Contact c')
      ->leftJoin('t.Professional pro')
      ->leftJoin('pro.Organism o')
      ->orderBy('e.name, m.happens_at, l.name, tck.price_name, u.first_name, u.last_name, tck.sf_guard_user_id, tck.cancelling IS NULL DESC, tck.updated_at');
    
    if ( !isset($criterias['not-yet-printed']) )
      $q->andWhere('tck.printed = TRUE OR tck.cancelling IS NOT NULL OR tck.integrated = TRUE');
    else
      $q->leftJoin('t.Payments p')
        ->andWhere('p.id IS NOT NULL');
    
    if ( !isset($criterias['tck_value_date_payment']) )
      $q->andWhere('tck.updated_at >= ? AND tck.updated_at < ?',array(
          date('Y-m-d',$dates[0]),
          date('Y-m-d',$dates[1]),
        ));
    else
    {
      if ( !$q->contains('LEFT JOIN t.Payments p') )
        $q->leftJoin('t.Payments p');
      $q->andWhere('p.created_at >= ? AND p.created_at < ?',array(
          date('Y-m-d',$dates[0]),
          date('Y-m-d',$dates[1]),
        ))
        ->andWhere('p.id = (SELECT min(id) FROM Payment p2 WHERE transaction_id = t.id)');
    }
    
    $q->andWhereIn('t.type',array('normal', 'cancellation'));
    
    // restrict access to our own user
    $q = $this->restrictQueryToCurrentUser($q);
    
    if ( isset($criterias['users']) && is_array($criterias['users']) && $criterias['users'][0] )
    {
      if ( $criterias['users'][''] ) unset($criterias['users']['']);
      if ( !isset($criterias['tck_value_date_payment']) )
        $q->andWhereIn('tck.sf_guard_user_id',$criterias['users']);
      else
      {
        if ( !$q->contains('LEFT JOIN t.Payments p') )
          $q->leftJoin('t.Payments p');
        $q->andWhereIn('p.sf_guard_user_id',$criterias['users']);
      }
    }
    
    if ( isset($criterias['workspaces']) && is_array($criterias['workspaces']) && $criterias['workspaces'][0] )
    {
      $q->andWhereIn('g.workspace_id',$criterias['workspaces']);
      $this->workspaces = $criterias['workspaces'];
    }

    // check if there are too much tickets to display them well
    $test = $q->copy();
    $events = $test->select('e.id, count(DISTINCT tck.id) AS nb_tickets')
      ->groupBy('e.id')
      ->orderBy('e.id')
      ->fetchArray();
    $this->nb_tickets = 0;
    foreach ( $events as $event )
      $this->nb_tickets += $event['nb_tickets'];
    
    // restrict the query if so...
    if ( $this->nb_tickets > sfConfig::get('app_ledger_max_tickets',5000) )
      $q->select('e.*, m.*, l.*');
    
    $this->events = $q->execute();
    $this->dates = $dates;
  }
  
  public function executeCash(sfWebRequest $request)
  {
    $criterias = $this->formatCriterias($request);
    $this->dates = $criterias['dates'];
    
    $q = $this->buildCashQuery($criterias);
    
    $this->methods = $q->execute();
  }
  
  protected function buildCashQuery($criterias)
  {
    $dates = $criterias['dates'];
    
    $q = Doctrine::getTable('PaymentMethod')->createQuery('m')
      ->leftJoin('m.Payments p')
      ->leftJoin('p.Transaction t')
      ->leftJoin('t.Contact c')
      ->leftJoin('t.Professional pro')
      ->leftJoin('pro.Organism o')
      ->leftJoin('p.User u')
      ->leftJoin('u.MetaEvents')
      ->leftJoin('u.Workspaces')
      ->orderBy('m.name, m.id, t.id, p.value, p.created_at');
    
    if ( is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 )
    {
      $q->andWhere('t.id IN (SELECT tck2.transaction_id FROM ticket tck2 WHERE tck2.manifestation_id IN ('.implode(',',$criterias['manifestations']).'))')
        ->leftJoin('t.Tickets tck')
        ->andWhere('tck.duplicate IS NULL')
        ->andWhere('tck.integrated = true OR tck.printed = true')
        ->andWhere('tck.cancelling IS NULL')
        ->andWhere('tck.id NOT IN (SELECT tck3.cancelling FROM ticket tck3 WHERE tck3.cancelling IS NOT NULL)');
    }
    else
    {
      $q->andWhere('p.created_at >= ? AND p.created_at < ?',array(
        date('Y-m-d',$dates[0]),
        date('Y-m-d',$dates[1]),
      ));
    }
    
    if ( isset($criterias['users']) && is_array($criterias['users']) && isset($criterias['users'][0]) )
      $q->andWhereIn('p.sf_guard_user_id',$criterias['users']);
    
    // restrict access to our own user
    $q = $this->restrictQueryToCurrentUser($q);
    
    return $q;
  }
  
  // restrict access to our own user
  protected static function restrictQueryToCurrentUser($q)
  {
    if ( !sfContext::getInstance()->getUser()->hasCredential('tck-ledger-all-users') )
    $q->andWhere('u.id = ?',sfContext::getInstance()->getUser()->getId());
    
    return $q;
  }
  
  public function executeBoth(sfWebRequest $request)
  {
    // filtering criterias
    $criterias = $this->formatCriterias($request);
    $dates = $criterias['dates'];
    if ( !isset($criterias['users']) ) $criterias['users'] = array();
    
    // by payment-type
    $q = new Doctrine_Query();
    $q->from('Transaction t')
      ->leftJoin('t.Payments p')
      ->leftJoin('p.Method pm')
      ->leftJoin('t.Tickets tck')
      ->leftJoin('p.User u')
      ->leftJoin('tck.Gauge g')
      ->andWhere('tck.duplicate IS NULL')
      ->andWhere('tck.printed = true OR tck.integrated = true OR tck.cancelling IS NOT NULL')
      ->orderBy('pm.name');
    if ( is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 )
    {
      $q->andWhere('t.id IN (SELECT tck2.transaction_id FROM ticket tck2 WHERE tck2.manifestation_id IN ('.implode(',',$criterias['manifestations']).'))');
    }
    else
    {
      $q->andWhere('tck.updated_at >= ? AND tck.updated_at < ?',array(
          date('Y-m-d',$dates[0]),
          date('Y-m-d',$dates[1]),
        ));
    }
    
    // restrict access to our own user
    $q = $this->restrictQueryToCurrentUser($q);
    
    if ( isset($criterias['users']) && is_array($criterias['users']) && isset($criterias['users'][0]) && $criterias['users'][0] )
      $q->andWhereIn('u.id',$criterias['users']);
    
    // optimizing stuff
    $q->select('t.id, p.id, p.value, pm.id, pm.name, u.id, sum(tck.value) AS value_tck_total')
      ->groupBy('t.id, p.id, p.value, pm.id, pm.name, u.id');
    if ( isset($criterias['manifestations']) && is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 )
      $q->addSelect('(sum(tck.value * CASE WHEN tck.manifestation_id IN ('.implode(',',$criterias['manifestations']).') THEN 1 ELSE 0 END)) AS value_tck_in_manifs');
    else
      $q->addSelect('sum(tck.value) AS value_tck_in_manifs');
    $transactions = $q->execute();
    
    $pm = array();
    foreach ( $transactions as $transaction )
    {
      $sum = array('total' => 0, 'partial' => 0);
      
      // optimizing stuff
      $sum['total'] += $transaction->value_tck_total;
      $sum['partial'] += $transaction->value_tck_in_manifs;
      
      if ( $sum['partial'] != 0 && $sum['total'] != 0 )
      foreach ( $transaction->Payments as $p )
      {
        if ( !isset($pm[$key = (string)$p->Method.' '.$p->payment_method_id]) )
          $pm[$key] = array('value+' => 0, 'value-' => 0, 'name' => (string)$p->Method, 'nb' => 0);
        $pm[$key][$p->value > 0 ? 'value+' : 'value-']
          += $p->value * $sum['partial']/$sum['total'];
        $pm[$key]['nb']++;
      }
    }
    
    ksort($pm);
    $this->byPaymentMethod = $pm;
    
    // by price
    $q = Doctrine::getTable('Price')->createQuery('p')
      ->leftJoin('p.Tickets t')
      ->leftJoin('t.Gauge g')
      ->leftJoin('t.User u')
      ->andWhere('t.printed OR t.cancelling IS NOT NULL OR t.integrated')
      ->andWhere('t.duplicate IS NULL')
      ->orderBy('p.name');
    if ( isset($criterias['users']) && is_array($criterias['users']) && $criterias['users'][0] )
      $q->andWhereIn('t.sf_guard_user_id',$criterias['users']);
    if ( isset($criterias['workspaces']) && is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0 )
      $q->andWhereIn('g.workspace_id',$criterias['workspaces']);
    if ( isset($criterias['manifestations']) && is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 )
      $q->andWhereIn('t.manifestation_id',$criterias['manifestations']);
    else
      $q->andWhere('t.updated_at >= ? AND t.updated_at < ?',array(
          date('Y-m-d',$dates[0]),
          date('Y-m-d',$dates[1]),
        ));

    // restrict access to our own user
    $q = $this->restrictQueryToCurrentUser($q);
    
    // optimizing stuff
    $q->select('p.id, p.name, p.description')
      ->addSelect('sum(t.value * CASE WHEN t.cancelling IS NOT NULL THEN 1 ELSE 0 END) AS tickets_cancelling_value')
      ->addSelect('sum(t.value * CASE WHEN t.cancelling IS     NULL THEN 1 ELSE 0 END) AS tickets_normal_value')
      ->addSelect('sum(t.cancelling IS NOT NULL) AS nb_cancelling')
      ->addSelect('count(t.id) AS nb_tickets')
      ->groupBy('p.id, p.name, p.description')
      ->orderBy('p.name');
    $this->byPrice = $q->execute();
    
    // by price's value
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $users = array();
    foreach ( $criterias['users'] as $user_id )
      $users[] = intval($user_id);
    $q = "SELECT value, count(id) AS nb, sum(value) AS total
          FROM ticket
          WHERE ".( is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 ? 'manifestation_id IN ('.implode(',',$criterias['manifestations']).')' : 'updated_at >= :date0 AND updated_at < :date1' )."
            AND id NOT IN (SELECT cancelling FROM ticket WHERE ".(!is_array($criterias['manifestations']) || count($criterias['manifestations']) == 0 ? 'updated_at >= :date0 AND updated_at < :date1 AND ' : '')." cancelling IS NOT NULL)
            AND cancelling IS NULL
            ".( is_array($criterias['users']) && count($criterias['users']) > 0 ? 'AND sf_guard_user_id IN ('.implode(',',$users).')' : '')."
            ".( is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0 ? 'AND gauge_id IN (SELECT id FROM gauge g WHERE g.workspace_id IN ('.implode(',',$criterias['workspaces']).'))' : '')."
            ".( !$this->getUser()->hasCredential('tck-ledger-all-users') ? 'AND sf_guard_user_id = '.sfContext::getInstance()->getUser()->getId() : '' )."
            AND (printed OR integrated OR cancelling IS NOT NULL)
            AND duplicate IS NULL
          GROUP BY value
          ORDER BY value DESC";
    //        ".( is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 ? 'manifestation_id IN ('.implode(',',$criterias['manifestations']).')' : '')."
    $stmt = $pdo->prepare($q);
    $stmt->execute(is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 ? NULL : array('date0' => date('Y-m-d',$dates[0]),'date1' => date('Y-m-d',$dates[1])));
    $this->byValue = $stmt->fetchAll();
    
    // synthesis by user
    $q = new Doctrine_Query();
    $q->from('SfGuardUser u')
      ->leftJoin('u.Tickets t')
      ->leftJoin('t.Gauge g')
      ->select('u.id, u.last_name, u.first_name, u.username')
      ->addSelect('(CASE WHEN sum(t.value >= 0) > 0 THEN sum(case when t.value < 0 then 0 else t.value end)/sum(t.value >= 0) ELSE 0 END) AS average')
      ->addSelect('sum(t.value = 0 AND t.cancelling IS NULL AND t.id NOT IN (SELECT t2.cancelling FROM ticket t2 WHERE t2.cancelling IS NOT NULL)) AS nb_free')
      ->addSelect('sum(t.value > 0 AND t.id NOT IN (SELECT t3.cancelling FROM ticket t3 WHERE t3.cancelling IS NOT NULL)) AS nb_paying')
      ->addSelect('sum(t.value <= 0 AND cancelling IS NOT NULL) AS nb_cancelling')
      ->addSelect('(CASE WHEN sum(t.value > 0) > 0 THEN sum(case when t.value < 0 then 0 else t.value end)/sum(t.value > 0) ELSE 0 END) AS average_paying')
      ->addSelect('sum(case when t.value < 0 then 0 else t.value end) AS income')
      ->addSelect('sum(case when t.value > 0 then 0 else t.value end) AS outcome')
      ->andWhere('t.duplicate IS NULL')
      ->andWhere('t.printed OR t.integrated OR t.cancelling IS NOT NULL')
      ->orderBy('u.last_name, u.first_name, u.username')
      ->groupBy('u.id, u.last_name, u.first_name, u.username');
    if ( is_array($criterias['users']) && count($criterias['users']) > 0 )
      $q->andWhereIn('t.sf_guard_user_id',$criterias['users']);
    if ( is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0 )
      $q->andWhereIn('g.workspace_id',$criterias['workspaces']);
    if ( is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 )
      $q->andWhereIn('t.manifestation_id',$criterias['manifestations']);
    else
      $q->andWhere('t.updated_at >= ? AND t.updated_at < ?',array(
        date('Y-m-d',$dates[0]),
        date('Y-m-d',$dates[1]),
      ));
    
    // restrict access to our own user
    $q = $this->restrictQueryToCurrentUser($q);
    
    $this->byUser = $q->execute();
    
    // get all selected manifestations
    $this->manifestations = false;
    if ( count($criterias['manifestations']) > 0 )
    {
      $q = Doctrine::getTable('Manifestation')->createQuery('m')
        ->andWhereIn('m.id',$criterias['manifestations']);
      $this->manifestations = $q->execute();
      
      $q = Doctrine::getTable('Gauge')->createQuery('g')
        ->leftJoin('g.Manifestation m')
        ->leftJoin('m.Event e')
        ->addSelect('(SELECT count(g2.id) FROM Manifestation m2 LEFT JOIN m2.Gauges g2 WHERE m2.id = g.manifestation_id AND g2.id IS NOT NULL) AS nb_ws')
        ->andWhereIn('g.manifestation_id',$criterias['manifestations'])
        ->orderBy('e.name, m.happens_at, ws.name');
      $this->gauges = $q->execute();
    }
  }
}
