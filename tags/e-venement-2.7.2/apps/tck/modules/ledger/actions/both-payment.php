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
    // by payment-type
    $q = new Doctrine_Query();
    $q->from('Transaction t')
      ->leftJoin('t.Payments p')
      ->leftJoin('p.Method pm')
      ->leftJoin('t.Tickets tck ON tck.transaction_id = t.id AND tck.duplicating IS NULL AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL OR tck.cancelling IS NOT NULL)')
      ->leftJoin('p.User u')
      ->leftJoin('tck.Gauge g')
      ->orderBy('pm.name');
    if ( isset($criterias['manifestations']) && is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 )
      $q->andWhere('t.id IN (SELECT tck2.transaction_id FROM ticket tck2 WHERE tck2.manifestation_id IN ('.implode(',',$criterias['manifestations']).'))');
    else
    {
      if ( isset($criterias['workspaces']) && is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0 )
        $q->andWhere('t.id IN (SELECT tck2.transaction_id FROM ticket tck2 LEFT JOIN tck2.Gauge g2 WHERE g2.workspace_id IN ('.implode(',',$criterias['workspaces']).'))');
      
      $q->andWhere('p.created_at >= ? AND p.created_at < ?',array(
        $dates[0],
        $dates[1],
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
      $q->addSelect('(sum(CASE WHEN tck.manifestation_id IN ('.implode(',',$criterias['manifestations']).') THEN tck.value ELSE 0 END)) AS value_tck_in_manifs');
    elseif ( isset($criterias['workspaces']) && is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0 )
      $q->addSelect('(sum(CASE WHEN g.workspace_id IN ('.implode(',',$criterias['workspaces']).') THEN tck.value ELSE 0 END)) AS value_tck_in_manifs');
    else
      $q->addSelect('sum(tck.value) AS value_tck_in_manifs');
    $transactions = $q->execute();
    
    $pm = array();
    foreach ( $transactions as $transaction )
    {
      // if ( $transaction->value_tck_total != 0 && $transaction->value_tck_manifs != 0 )
      foreach ( $transaction->Payments as $p )
      {
        if ( !isset($pm[$key = (string)$p->Method.' '.$p->payment_method_id]) )
          $pm[$key] = array('value+' => 0, 'value-' => 0, 'name' => (string)$p->Method, 'nb' => 0);
        $pm[$key][$p->value > 0 ? 'value+' : 'value-']
          += $p->value * abs($transaction->value_tck_total == 0 ? 1 : $transaction->value_tck_in_manifs/$transaction->value_tck_total); // abs() to avoid "-10 * -30/+10" which, normally, won't happens but anyway...
        $pm[$key]['nb']++;
      }
    }
    
    ksort($pm);
    $this->byPaymentMethod = $pm;
