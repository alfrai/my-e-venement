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
    foreach ( $q->execute() as $user )
      $this->json['sales']['booked-by-one'][] = array('user' => (string)$user, 'nb' => $user->nb);
    
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
    foreach ( $q->execute() as $user )
      $this->json['sales']['paid-by-one-prepared-by-another'][] = array('user' => (string)$user, 'nb' => $user->nb);
    
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
    ;
    $q = Doctrine_Query::create()->from('sfGuardUser u')
      ->select("u.*, ($q2) AS nb")
      ->addParams('where', $q2->getFlattenedParams())
      ->orderBy('u.username');
    foreach ( $q->execute() as $user )
      $this->json['sales']['to-be-paid'][] = array('user' => (string)$user, 'nb' => $user->nb);
    
    // seats available of online sales
    $prepare = array();
    foreach ( sfConfig::get('app_manifestation_online_users', array()) as $u )
      $prepare[] = '?';
    $prepare = '('.implode(',', $prepare).')';
    $q = Doctrine::getTable('Manifestation')->createQuery('m', true)
      ->andWhere('m.id = ?', $request->getParameter('id', 0))
      
      ->leftJoin('me.Users meu')
      ->andWhereIn('meu.username', sfConfig::get('app_manifestation_online_users', array()))
      
      ->leftJoin('m.Gauges g')
      ->andWhere('g.online = ?', true)
      
      ->leftJoin('l.SeatedPlans sp')
      ->leftJoin('sp.Seats s')
      ->leftJoin('s.Tickets tck WITH tck.manifestation_id = m.id')
      ->andWhere('tck.id IS NULL')
      
      ->leftJoin('sp.Workspaces ws')
      ->leftJoin('ws.Users wsu')
      ->andWhere('g.workspace_id = ws.id')
      ->andWhereIn('wsu.username', sfConfig::get('app_manifestation_online_users', array()))
      
      ->leftJoin('m.Prices mp')
      ->leftJoin('mp.Users mpu WITH mpu.username IN '.$prepare, sfConfig::get('app_manifestation_online_users', array()))
      ->leftJoin('g.Prices gp')
      ->leftJoin('gp.Users gpu WITH gpu.username IN '.$prepare, sfConfig::get('app_manifestation_online_users', array()))
      ->andWhere('gpu.id IS NOT NULL OR mpu.id IS NOT NULL')
      
      ->select('count(DISTINCT s.id) AS nb')
    ;
    $data = $q->fetchArray();
    $this->json['gauges']['online'] = $data[0]['nb'];
    
    // seats not available online for many reasons
    $q = Doctrine::getTable('Manifestation')->createQuery('m', true)
      ->andWhere('m.id = ?', $request->getParameter('id', 0))
      ->leftJoin('me.Users meu')
      ->andWhereIn('meu.username', sfConfig::get('app_manifestation_online_users', array()))
      
      ->leftJoin('m.Gauges g')
      
      ->leftJoin('l.SeatedPlans sp')
      ->leftJoin('sp.Seats s')
      ->leftJoin('s.Tickets tck WITH tck.manifestation_id = m.id')
      ->andWhere('tck.id IS NULL')
      
      ->leftJoin('sp.Workspaces ws')
      ->leftJoin('ws.Users wsu')
      ->andWhere('g.workspace_id = ws.id')
      ->andWhereIn('wsu.username', sfConfig::get('app_manifestation_online_users', array()))
      
      ->leftJoin('m.Prices mp')
      ->leftJoin('mp.Users mpu WITH mpu.username IN '.$prepare, sfConfig::get('app_manifestation_online_users', array()))
      ->leftJoin('g.Prices gp')
      ->leftJoin('gp.Users gpu WITH gpu.username IN '.$prepare, sfConfig::get('app_manifestation_online_users', array()))
      
      ->andWhere('g.online = ? OR gpu.id IS NULL AND mpu.id IS NULL', false)
      
      ->select('count(DISTINCT s.id) AS nb')
    ;
    $data = $q->fetchArray();
    $this->json['gauges']['offline'] = $data[0]['nb'];
    
    // total of free seats, in open AND in closed situations
    $this->json['gauges']['total'] = $this->json['gauges']['online'] + $this->json['gauges']['offline'];
