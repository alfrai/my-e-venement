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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $this->getContext()->getConfiguration()->loadHelpers('Number');
    
    $this->json = array(
      'seats' => array(
        'free' => array(
          'online' => array(
            'nb' => 0,
            'min' => array('money' => 0, 'money_txt' => ''),
            'max' => array('money' => 0, 'money_txt' => ''),
          ),
          'all' => array(
            'nb' => 0,
            'min' => array('money' => 0, 'money_txt' => ''),
            'max' => array('money' => 0, 'money_txt' => ''),
          ),
        ),
        'ordered' => array(
          'online' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
          'all' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
        ),
        'printed' => array(
          'online' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
          'all' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
        ),
      ),
      'gauges' => array(
        'free' => array(
          'online' => array(
            'nb' => 0,
            'min' => array('money' => 0, 'money_txt' => ''),
            'max' => array('money' => 0, 'money_txt' => ''),
          ),
          'all' => array(
            'nb' => 0,
            'min' => array('money' => 0, 'money_txt' => ''),
            'max' => array('money' => 0, 'money_txt' => ''),
          ),
        ),
        'ordered' => array(
          'online' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
          'all' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
        ),
        'printed' => array(
          'online' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
          'all' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
        ),
      ),
    );
    
    // seats available and unavailable for online sales
    
    $users = array();
    foreach ( Doctrine::getTable('sfGuardUser')->createQuery('u')
      ->andWhereIn('u.username', sfConfig::get('app_manifestation_online_users', array()))
      ->select('u.id')
      ->fetchArray() as $user )
      $users[] = $user['id'];
    
    $prepare = array();
    foreach ( $users as $u )
      $prepare[] = '?';
    $prepare = '('.implode(',', $prepare).')';
    
    $q = Doctrine::getTable('Manifestation')->createQuery('m',true)
      ->leftJoin('m.Gauges g')
/*
    $q = Doctrine::getTable('Gauge')->createQuery('g', false)
      ->andWhere('g.manifestation_id = ?', $request->getParameter('id', 0))
      ->leftJoin('g.Workspace ws')
      ->leftJoin('g.Manifestation m')
      ->leftJoin('m.Event e')
      ->leftJoin('e.MetaEvent me')
*/
      
      ->leftJoin('g.Tickets tck')
      ->leftJoin('tck.Seat s')
      ->leftJoin('tck.Transaction t')
      ->leftJoin('t.Order o')
      
      // problem here, duplicatas need to get the last ticket to get the seat_id, but cancellations need the first ticket...
      ->leftJoin('tck.Duplicatas d')
      ->leftJoin('tck.Cancelling c')
      
      ->addSelect('m.*, g.*, tck.*')
      ->andWhere('tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL OR o.id IS NOT NULL') // printed or integrated or booked by an order
      ->andWhere('tck.cancelling IS NULL')
    ;
    $online = $q->copy()
      ->andWhere('(SELECT count(wsu.sf_guard_user_id) FROM WorkspaceUser wsu WHERE wsu.sf_guard_user_id IN '.$prepare.') > 0', $users)
      ->andWhere('(SELECT count(meu.sf_guard_user_id) FROM MetaEventUser meu WHERE meu.sf_guard_user_id IN '.$prepare.') > 0', $users)
      ->andWhere('(TRUE')
      ->andWhere('(SELECT count(mpu.sf_guard_user_id) FROM UserPrice mpu LEFT JOIN mpu.Price mp LEFT JOIN mp.PriceManifestation pm WHERE pm.manifestation_id = m.id AND mpu.sf_guard_user_id IN '.$prepare.') > 0', $users)
      ->orWhere('(SELECT count(gpu.sf_guard_user_id) FROM UserPrice gpu LEFT JOIN gpu.Price gp LEFT JOIN gp.PriceGauge pg WHERE pg.gauge_id = g.id AND gpu.sf_guard_user_id IN '.$prepare.') > 0', $users)
      ->andWhere('TRUE)')
      ->andWhere('g.online = ?', true)
    ;
    $all = $q;
    foreach ( array('online', 'all') as $type )
    foreach ( $$type->execute() as $manif )
    foreach ( $manif->Gauges as $gauge )
//    foreach ( $$type->execute() as $gauge )
    foreach ( $gauge->Tickets as $ticket )
    {
      // cancelled
      $orig = $ticket->getOriginal();
      if ( $orig->Cancelling->count() > 0 || $ticket->Cancelling->count() > 0 )
        continue;
      
      $state = $ticket->printed_at || $ticket->integrated_at ? 'printed' : 'ordered';
      if ( $orig->identifier() == $ticket->identifier() )
      {
        $this->json['gauges'][$state][$type]['nb']++;
        $this->json['gauges'][$state][$type]['money'] += $ticket->value;
      }
      
      if ( !is_null($ticket->seat_id) )
      {
        $this->json['seats'][$state][$type]['nb']++;
        $this->json['seats'][$state][$type]['money'] += $ticket->value;
      }
    }
    
    // the "text" values with currency
    foreach ( array('online', 'all') as $type )
    foreach ( array('printed', 'ordered') as $state )
    foreach ( array('seats', 'gauges') as $value )
      $this->json[$value][$state][$type]['money_txt'] = format_currency($this->json[$value][$state][$type]['money'], '€');
    
    // Free seats & gauges
    $o = Doctrine_Query::create()->from('Order oo')
      ->select('count(oo.id)')
      ->andWhere('oo.transaction_id = tck.transaction_id')
    ;
    $q = Doctrine::getTable('Gauge')->createQuery('g',false)
      ->leftJoin('g.Workspace ws')
      ->leftJoin('ws.Users wsu WITH wsu.id IN '.$prepare, $users)
      
      ->leftJoin('g.Manifestation m')
      ->andWhere('m.id = ?', $request->getParameter('id', 0))
      
      ->leftJoin('m.Event e')
      ->leftJoin('e.MetaEvent me')
      ->leftJoin('me.Users meu WITH meu.id IN '.$prepare, $users)
      
      ->leftJoin('m.Location l')
      ->leftJoin('l.SeatedPlans sp')
      ->leftJoin('sp.Workspaces spw WITH spw.id = ws.id')
      ->leftJoin('sp.Seats s WITH spw.id IS NOT NULL')
      ->leftJoin('s.Tickets tck WITH tck.gauge_id = g.id AND (tck.cancelling IS NULL AND (tck.integrated_at IS NOT NULL OR tck.printed_at IS NOT NULL OR ('.$o.') > 0))')
      
      ->leftJoin('g.Prices gp')
      ->leftJoin('gp.Users gpu WITH gpu.id IN '.$prepare, $users)
      ->leftJoin('m.Prices mp')
      ->leftJoin('mp.Users mpu WITH mpu.id IN '.$prepare, $users)
      
      ->andWhere('tck.id IS NULL')
      ->groupBy('g.id, g.online, g.value, g.workspace_id')
      ->select('g.id, g.online, g.workspace_id, count(DISTINCT s.id) AS nb')
    ;
    
    // free online seats
    $q1 = $q->copy()
      ->andWhere('wsu.id IS NOT NULL AND meu.id IS NOT NULL')
      ->andWhere('gpu.id IS NOT NULL OR mpu.id IS NOT NULL')
      ->andWhere('g.online = ?', true)
    ;
    $gauges = array();
    foreach ( $q1->execute() as $gauge )
    {
      $gauges[$gauge->id] = $gauge->nb;
      $this->json['seats']['free']['online']['nb'] += $gauge['nb'];
      $this->json['seats']['free']['online']['min']['money'] += $gauge->nb * $gauge->getPriceMin($users);
      $this->json['seats']['free']['online']['max']['money'] += $gauge->nb * $gauge->getPriceMax($users);
    }
    $this->json['seats']['free']['online']['min']['money_txt'] = format_currency($this->json['seats']['free']['online']['min']['money'], '€');
    $this->json['seats']['free']['online']['max']['money_txt'] = format_currency($this->json['seats']['free']['online']['max']['money'], '€');
    
    // free all seats
    foreach ( $q->execute() as $gauge )
    {
      $this->json['seats']['free']['all']['nb'] += $gauge->nb;
      $this->json['seats']['free']['all']['min']['money'] += $gauge->nb * $gauge->getPriceMin($users);
      $this->json['seats']['free']['all']['max']['money'] += $gauge->nb * $gauge->getPriceMax($users);
    }
    $this->json['seats']['free']['all']['min']['money_txt'] = format_currency($this->json['seats']['free']['all']['min']['money'], '€');
    $this->json['seats']['free']['all']['max']['money_txt'] = format_currency($this->json['seats']['free']['all']['max']['money'], '€');
    
    // free gauges
    $q = Doctrine::getTable('Gauge')->createQuery('g')
      ->andWhere('g.manifestation_id = ?', $request->getParameter('id', 0))
    ;
    // online
    foreach ( $gauges = $q->copy()
      ->leftJoin('g.Manifestation m')
      ->leftJoin('m.Event e')
      ->leftJoin('e.MetaEvent me')
      
      ->leftJoin('ws.Users wsu')
      ->andWhereIn('wsu.id', $users)
      ->leftJoin('me.Users meu')
      ->andWhereIn('meu.id', $users)
      
      ->leftJoin('g.Prices gp')
      ->leftJoin('gp.Users gpu WITH gpu.id IN '.$prepare, $users)
      ->leftJoin('m.Prices mp')
      ->leftJoin('mp.Users mpu WITH mpu.id IN '.$prepare, $users)
      
      ->andWhere('gpu.id IS NOT NULL OR mpu.id IS NOT NULL')
      ->andWhere('g.online = ?', true)
      ->execute() as $gauge )
    {
      $this->json['gauges']['free']['online']['nb'] += $gauge->value - $gauge->printed - $gauge->ordered;
      $this->json['gauges']['free']['online']['min']['money'] += ($gauge->value - $gauge->printed - $gauge->ordered) * $gauge->getPriceMin($users);
      $this->json['gauges']['free']['online']['max']['money'] += ($gauge->value - $gauge->printed - $gauge->ordered) * $gauge->getPriceMax($users);
    }
    $this->json['gauges']['free']['online']['min']['money_txt'] = format_currency($this->json['gauges']['free']['online']['min']['money'], '€');
    $this->json['gauges']['free']['online']['max']['money_txt'] = format_currency($this->json['gauges']['free']['online']['max']['money'], '€');
    // all
    foreach ( $gauges = $q->copy()
      ->execute() as $gauge )
    {
      $this->json['gauges']['free']['all']['nb'] += $gauge->value - $gauge->printed - $gauge->ordered;
      $this->json['gauges']['free']['all']['min']['money'] += ($gauge->value - $gauge->printed - $gauge->ordered) * $gauge->getPriceMin($users);
      $this->json['gauges']['free']['all']['max']['money'] += ($gauge->value - $gauge->printed - $gauge->ordered) * $gauge->getPriceMax($users);
    }
    $this->json['gauges']['free']['all']['min']['money_txt'] = format_currency($this->json['gauges']['free']['all']['min']['money'], '€');
    $this->json['gauges']['free']['all']['max']['money_txt'] = format_currency($this->json['gauges']['free']['all']['max']['money'], '€');
