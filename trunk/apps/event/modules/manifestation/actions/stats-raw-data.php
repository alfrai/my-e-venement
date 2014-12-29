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
*    Foundation, Inc., 5'.$rank.' Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $this->json = array(
      'sales'  => array(
        'booked-by-one' => array(),
        'booked-by-one-prepared-by-another' => array(),
        'to-be-paid' => array(),
      ),
      'gauges' => array(),
    );
    
    // tickets booked & paid by the same person
    $q2 = Doctrine::getTable('Transaction')->createQuery('t')
      ->leftJoin('t.Version v')
      ->leftJoin('t.Payments p')
      ->andWhere('tck.id IS NOT NULL')
      ->andWhere('tck.manifestation_id = ?', $request->getParameter('id', 0))
      ->andWhere('v.version = 1')
      ->andWhere('v.sf_guard_user_id = p.sf_guard_user_id')
      ->select('count(tck.id) AS nb')
      ->andWhere('v.sf_guard_user_id = u.id')
    ;
    $q = Doctrine_Query::create()->from('sfGuardUser u')
      ->select("u.*, ($q2) AS nb")
      ->addParams('where', $q2->getFlattenedParams())
      ->orderBy('u.username');
    $total = 0;
    foreach ( $q->execute() as $user )
    {
      $total += $user->nb;
      $this->json['sales']['booked-by-one'][] = array('user' => (string)$user, 'nb' => $user->nb);
    }
    $this->json['sales']['booked-by-one'][] = array('user' => 'Total', 'nb' => $total);
    
    // tickets booked offline & paid online
    $q2 = Doctrine::getTable('Transaction')->createQuery('t')
      ->leftJoin('t.Version v')
      ->leftJoin('t.Payments p')
      ->andWhere('tck.id IS NOT NULL')
      ->andWhere('tck.manifestation_id = ?', $request->getParameter('id', 0))
      ->andWhere('v.version = 1')
      ->andWhere('v.sf_guard_user_id != p.sf_guard_user_id')
      ->select('count(tck.id) AS nb')
      ->andWhere('p.sf_guard_user_id = u.id')
    ;
    $q = Doctrine_Query::create()->from('sfGuardUser u')
      ->select("u.*, ($q2) AS nb")
      ->addParams('where', $q2->getFlattenedParams())
      ->orderBy('u.username');
    $total = 0;
    foreach ( $q->execute() as $user )
    {
      $total += $user->nb;
      $this->json['sales']['paid-by-one-prepared-by-another'][] = array('user' => (string)$user, 'nb' => $user->nb);
    }
    $this->json['sales']['paid-by-one-prepared-by-another'][] = array('user' => 'Total', 'nb' => $total);
    
    // tickets prepared but still unpaid
    $q2 = Doctrine::getTable('Transaction')->createQuery('t')
      ->leftJoin('t.Order o')
      ->leftJoin('t.Payments p')
      ->andWhere('p.id IS NULL')
      ->andWhere('tck.id IS NOT NULL')
      ->andWhere('o.id IS NOT NULL')
      ->andWhere('tck.manifestation_id = ?', $request->getParameter('id', 0))
      ->select('count(tck.id) AS nb')
      ->andWhere('o.sf_guard_user_id = u.id')
      ->groupBy('o.sf_guard_user_id')
      ->having('sum(tck.value) > 0')
    ;
    $q = Doctrine_Query::create()->from('sfGuardUser u')
      ->select("u.*, ($q2) AS nb")
      ->addParams('where', $q2->getFlattenedParams())
      ->orderBy('u.username');
    $total = 0;
    foreach ( $q->execute() as $user )
    {
      $total += $user->nb;
      $this->json['sales']['to-be-paid'][] = array('user' => (string)$user, 'nb' => is_null($user->nb) ? 0 : $user->nb);
    }
    $this->json['sales']['to-be-paid'][] = array('user' => 'Total', 'nb' => $total);
    
    // tickets prepared, seated, but still unpaid
    $q2->andWhere('tck.seat_id IS NOT NULL');
    $q = Doctrine_Query::create()->from('sfGuardUser u')
      ->select("u.*, ($q2) AS nb")
      ->addParams('where', $q2->getFlattenedParams())
      ->orderBy('u.username');
    $total = 0;
    foreach ( $q->execute() as $user )
    {
      $total += $user->nb;
      $this->json['sales']['seated-to-be-paid'][] = array('user' => (string)$user, 'nb' => is_null($user->nb) ? 0 : $user->nb);
    }
    $this->json['sales']['seated-to-be-paid'][] = array('user' => 'Total', 'nb' => $total);
    
    // seats available and unavailable for online sales
    $prepare = array();
    foreach ( sfConfig::get('app_manifestation_online_users', array()) as $u )
      $prepare[] = '?';
    $prepare = '('.implode(',', $prepare).')';
    $o = Doctrine_Query::create()->from('Order oo')
      ->select('count(oo.id)')
      ->andWhere('oo.transaction_id = tck.transaction_id')
    ;
    $q = Doctrine::getTable('Gauge')->createQuery('g',false)
      ->leftJoin('g.Workspace ws')
      ->leftJoin('ws.Users wsu')
      
      ->leftJoin('g.Manifestation m')
      ->andWhere('m.id = ?', $request->getParameter('id', 0))
      
      ->leftJoin('m.Event e')
      ->leftJoin('e.MetaEvent me')
      ->leftJoin('me.Users meu')
      
      ->leftJoin('m.Location l')
      ->leftJoin('l.SeatedPlans sp')
      ->leftJoin('sp.Workspaces spw WITH spw.id = ws.id')
      ->leftJoin('sp.Seats s WITH spw.id IS NOT NULL')
      ->leftJoin('s.Tickets tck WITH tck.manifestation_id = m.id AND (tck.integrated_at IS NOT NULL OR tck.printed_at IS NOT NULL OR ('.$o.') > 0)')
      ->andWhere('tck.id IS NULL')
      
      ->leftJoin('m.Prices mp')
      ->leftJoin('mp.Users mpu WITH mpu.username IN '.$prepare, sfConfig::get('app_manifestation_online_users', array()))
      ->leftJoin('g.Prices gp')
      ->leftJoin('gp.Users gpu WITH gpu.username IN '.$prepare, sfConfig::get('app_manifestation_online_users', array()))
      
      ->groupBy('g.id, g.online, g.value, g.workspace_id')
      ->select('g.id, g.online, g.workspace_id, count(DISTINCT s.id) AS nb')
    ;
    
    $q1 = $q->copy()
      ->andWhereIn('wsu.username', sfConfig::get('app_manifestation_online_users', array()))
      ->andWhereIn('meu.username', sfConfig::get('app_manifestation_online_users', array()))
      ->having('g.online = ? AND COUNT(gpu.id) > 0 OR COUNT(mpu.id) > 0', true)
    ;
    $this->json['gauges']['online'] = 0;
    foreach ( $q1->fetchArray() as $gauge )
      $this->json['gauges']['online'] += $gauge['nb'];
    
    $q2 = $q->copy()
      ->andWhereNotIn('wsu.username', sfConfig::get('app_manifestation_online_users', array()))
      ->andWhereNotIn('meu.username', sfConfig::get('app_manifestation_online_users', array()))
      ->having('g.online = ? OR COUNT(gpu.id) = 0 AND COUNT(mpu.id) = 0', false)
    ;
    $this->json['gauges']['offline'] = 0;
    foreach ( $q2->fetchArray() as $gauge )
      $this->json['gauges']['offline'] += $gauge['nb'];
    
    // total of free seats, in open AND in closed situations
    $this->json['gauges']['total'] = $this->json['gauges']['online'] + $this->json['gauges']['offline'];
