<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2012 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2012 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    // filtering criterias
    $this->options = $criterias = $this->formatCriterias($request);
    $dates = $criterias['dates'];
    if ( !isset($criterias['users']) )
      $criterias['users'] = array();
    
    // redirect to avoid POST re-sending
    if ( $request->getParameter($this->form->getName(),false) )
      $this->redirect('ledger/both');
    
    // by payment-type
    $q = new Doctrine_Query();
    $q->from('Transaction t')
      ->leftJoin('t.Payments p')
      ->leftJoin('p.Method pm')
      ->leftJoin('t.Tickets tck')
      ->leftJoin('p.User u')
      ->leftJoin('tck.Gauge g')
      ->andWhere('tck.duplicating IS NULL')
      ->andWhere('tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL OR tck.cancelling IS NOT NULL')
      ->orderBy('pm.name');
    if ( is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 )
      $q->andWhere('t.id IN (SELECT tck2.transaction_id FROM ticket tck2 WHERE tck2.manifestation_id IN ('.implode(',',$criterias['manifestations']).'))');
    else
      $q->andWhere('tck.printed_at IS NOT NULL AND tck.printed_at >= ? AND tck.printed_at < ? OR tck.integrated_at IS NOT NULL AND tck.integrated_at >= ? AND tck.integrated_at < ?',array(
          $dates[0],
          $dates[1],
          $dates[0],
          $dates[1],
        ));
    
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
      ->andWhere('t.printed_at IS NOT NULL OR t.cancelling IS NOT NULL OR t.integrated_at IS NOT NULL')
      ->andWhere('t.duplicating IS NULL')
      ->orderBy('p.name');
    if ( isset($criterias['users']) && is_array($criterias['users']) && $criterias['users'][0] )
      $q->andWhereIn('t.sf_guard_user_id',$criterias['users']);
    if ( isset($criterias['workspaces']) && is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0 )
      $q->andWhereIn('g.workspace_id',$criterias['workspaces']);
    if ( isset($criterias['manifestations']) && is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 )
      $q->andWhereIn('t.manifestation_id',$criterias['manifestations']);
    else
      $q->andWhere('t.printed_at IS NOT NULL AND t.printed_at >= ? AND t.printed_at < ? OR t.integrated_at IS NOT NULL AND t.integrated_at >= ? AND t.integrated_at < ?',array(
          $dates[0],
          $dates[1],
          $dates[0],
          $dates[1],
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
          WHERE ".( is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 ? 'manifestation_id IN ('.implode(',',$criterias['manifestations']).')' : '(printed_at IS NOT NULL AND printed_at >= :date0 AND printed_at < :date1 OR integrated_at IS NOT NULL AND integrated_at >= :date0 AND integrated_at < :date1)' )."
            AND id NOT IN (SELECT cancelling FROM ticket WHERE ".(!is_array($criterias['manifestations']) || count($criterias['manifestations']) == 0 ? '(printed_at IS NOT NULL AND printed_at >= :date0 AND printed_at < :date1 OR integrated_at IS NOT NULL AND integrated_at >= :date0 AND integrated_at < :date1) AND ' : '')." cancelling IS NOT NULL AND duplicating IS NULL)
            AND cancelling IS NULL
            ".( is_array($criterias['users']) && count($criterias['users']) > 0 ? 'AND sf_guard_user_id IN ('.implode(',',$users).')' : '')."
            ".( is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0 ? 'AND gauge_id IN (SELECT id FROM gauge g WHERE g.workspace_id IN ('.implode(',',$criterias['workspaces']).'))' : '')."
            ".( !$this->getUser()->hasCredential('tck-ledger-all-users') ? 'AND sf_guard_user_id = '.sfContext::getInstance()->getUser()->getId() : '' )."
            AND (printed_at IS NOT NULL OR integrated_at IS NOT NULL OR cancelling IS NOT NULL)
            AND duplicating IS NULL
          GROUP BY value
          ORDER BY value DESC";
    //        ".( is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 ? 'manifestation_id IN ('.implode(',',$criterias['manifestations']).')' : '')."
    $stmt = $pdo->prepare($q);
    $stmt->execute(is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 ? NULL : array('date0' =>$dates[0],'date1' => $dates[1]));
    $this->byValue = $stmt->fetchAll();
    
    // synthesis by user
    $q = new Doctrine_Query();
    $q->from('SfGuardUser u')
      ->leftJoin('u.Tickets t')
      ->leftJoin('t.Gauge g')
      ->select('u.id, u.last_name, u.first_name, u.username')
      ->addSelect('(CASE WHEN sum(t.value >= 0) > 0 THEN sum(case when t.value < 0 then 0 else t.value end)/sum(t.value >= 0) ELSE 0 END) AS average')
      ->addSelect('sum(t.value = 0 AND t.cancelling IS NULL AND t.id NOT IN (SELECT t2.cancelling FROM ticket t2 WHERE t2.cancelling IS NOT NULL AND t2.duplicating IS NULL)) AS nb_free')
      ->addSelect('sum(t.value > 0 AND t.id NOT IN (SELECT t3.cancelling FROM ticket t3 WHERE t3.cancelling IS NOT NULL AND t3.duplicating IS NULL)) AS nb_paying')
      ->addSelect('sum(t.value <= 0 AND cancelling IS NOT NULL) AS nb_cancelling')
      ->addSelect('(CASE WHEN sum(t.value > 0) > 0 THEN sum(case when t.value < 0 then 0 else t.value end)/sum(t.value > 0) ELSE 0 END) AS average_paying')
      ->addSelect('sum(case when t.value < 0 then 0 else t.value end) AS income')
      ->addSelect('sum(case when t.value > 0 then 0 else t.value end) AS outcome')
      ->andWhere('t.duplicating IS NULL')
      ->andWhere('t.printed_at IS NOT NULL OR t.integrated_at IS NOT NULL OR t.cancelling IS NOT NULL')
      ->orderBy('u.last_name, u.first_name, u.username')
      ->groupBy('u.id, u.last_name, u.first_name, u.username');
    if ( is_array($criterias['users']) && count($criterias['users']) > 0 )
      $q->andWhereIn('t.sf_guard_user_id',$criterias['users']);
    if ( is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0 )
      $q->andWhereIn('g.workspace_id',$criterias['workspaces']);
    if ( is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 )
      $q->andWhereIn('t.manifestation_id',$criterias['manifestations']);
    else
      $q->andWhere('t.printed_at IS NOT NULL AND t.printed_at >= ? AND t.printed_at < ? OR t.integrated_at IS NOT NULL AND t.integrated_at >= ? AND t.integrated_at < ?',array(
          $dates[0],
          $dates[1],
          $dates[0],
          $dates[1],
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
