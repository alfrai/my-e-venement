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
    $this->redirect('ledger/sales');
  }
  
  protected function formatCriterias(sfWebRequest $request)
  {
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
      $this->getUser()->setFlash('error','Submitted values are invalid');
    }
    
    $dates = array(
      $criterias['dates']['from']['day']
        ? strtotime($criterias['dates']['from']['year'].'-'.$criterias['dates']['from']['month'].'-'.$criterias['dates']['from']['day'])
        : strtotime('1 week ago 0:00'),
      $criterias['dates']['to']['day']
        ? strtotime($criterias['dates']['to']['year'].'-'.$criterias['dates']['to']['month'].'-'.$criterias['dates']['to']['day'])
        : strtotime('tomorrow 0:00'),
    );
    
    if ( $dates[0] > $dates[1] )
    {
      $buf = $dates[1];
      $dates[1] = $dates[0];
      $dates[0] = $buf;
    }
    $criterias['dates'] = $dates;
    
    return $criterias;
  }
  
  public function executeSales(sfWebRequest $request)
  {
    $criterias = $this->formatCriterias($request);
    $dates = $criterias['dates'];
    
    $q = Doctrine::getTable('Event')->createQuery('e')
      ->leftJoin('e.Manifestations m')
      ->leftJoin('m.Location l')
      ->leftJoin('m.Tickets tck')
      ->leftJoin('tck.Transaction t')
      ->leftJoin('tck.Gauge g')
      ->leftJoin('t.Contact c')
      ->leftJoin('t.Professional pro')
      ->leftJoin('pro.Organism o')
      ->leftJoin('tck.User u')
      ->andWhere('tck.duplicate IS NULL')// OR (tck.cancelling IS NOT NULL AND tck.cancelling IN (SELECT tck2.cancelling FROM Ticket tck2 WHERE tck2.cancelling = tck.cancelling AND tck2.id != tck.id))')
      ->andWhere('tck.printed = TRUE OR tck.cancelling IS NOT NULL OR tck.integrated = TRUE')
      ->andWhere('tck.updated_at >= ? AND tck.updated_at < ?',array(
        date('Y-m-d',$dates[0]),
        date('Y-m-d',$dates[1]),
      ))
      ->orderBy('e.name, m.happens_at, l.name, tck.price_name, u.first_name, u.last_name, tck.sf_guard_user_id, tck.cancelling IS NULL DESC, tck.updated_at');
    
    $q->andWhereIn('t.type',array('normal', 'cancellation'));
    
    if ( isset($criterias['users']) && is_array($criterias['users']) && $criterias['users'][0] )
      $q->andWhereIn('tck.sf_guard_user_id',$criterias['users']);

    if ( isset($criterias['workspaces']) && is_array($criterias['workspaces']) && $criterias['workspaces'][0] )
      $q->andWhereIn('g.workspace_id',$criterias['workspaces']);

    $this->events = $q->execute();
    $this->dates = $dates;
  }
  
  public function executeCash(sfWebRequest $request)
  {
    $criterias = $this->formatCriterias($request);
    $dates = $criterias['dates'];
    
    $q = $this->buildCashQuery($criterias);
    $this->methods = $q->execute();
    $this->dates = $dates;
  }
  
  protected function buildCashQuery($criterias)
  {
    $dates = $criterias['dates'];
    
    $q = Doctrine::getTable('PaymentMethod')->createQuery('m')
      ->leftJoin('m.Payments p')
      ->leftJoin('p.Transaction t')
      //->leftJoin('t.Contact c')
      //->leftJoin('t.Professional pro')
      //->leftJoin('pro.Organism o')
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
      ->leftJoin('tck.Gauge g')
      ->andWhere('tck.duplicate IS NULL')
      ->andWhere('tck.printed = true OR tck.integrated = true OR tck.cancelling IS NOT NULL')
      ->orderBy('pm.name');
    if ( is_array($criterias['manifestations']) )
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
    $transactions = $q->execute();
    
    $pm = array();
    foreach ( $transactions as $transaction )
    {
      $sum = array('total' => 0, 'partial' => 0);
      
      foreach ( $transaction->Tickets as $t )
      {
        $sum['total'] += $t->value;
        if ( (in_array($t->manifestation_id,$criterias['manifestations']) || count($criterias['manifestations']) == 0)
          && (in_array($t->Gauge->workspace_id,$criterias['workspaces']) || count($criterias['workspaces']) == 0) )
        {
          $sum['partial'] += $t->value;
        }
      }

      if ( $sum['partial'] != 0 && $sum['total'] != 0 )
      foreach ( $transaction->Payments as $p )
      {
        if ( !isset($pm[$p->payment_method_id]) )
          $pm[$p->payment_method_id] = array('value+' => 0, 'value-' => 0, 'name' => (string)$p->Method, 'nb' => 0);
        $pm[$p->payment_method_id][$p->value > 0 ? 'value+' : 'value-']
          += $p->value * $sum['partial']/$sum['total'];
        $pm[$p->payment_method_id]['nb']++;
      }
    }
    
    $this->byPaymentMethod = $pm;
    
    // by price
    $q = Doctrine::getTable('Price')->createQuery('p')
      ->leftJoin('p.Tickets t')
      ->leftJoin('t.Gauge g')
      ->andWhere('t.printed OR t.cancelling IS NOT NULL OR t.integrated')
      ->andWhere('t.duplicate IS NULL')
      ->orderBy('p.name');
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
    $this->byUser = $q->execute();
    
    // get all selected manifestations
    $this->manifestations = false;
    if ( count($criterias['manifestations']) > 0 )
    {
      $q = Doctrine::getTable('Manifestation')->createQuery('m')
        ->andWhereIn('m.id',$criterias['manifestations']);
      $this->manifestations = $q->execute();
    }
  }
}
