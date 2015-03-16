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
    $path = liCacher::componePath($request->getUri());
    if ( !$request->hasParameter('refresh')
      && ($this->json = liCacher::create($path)->useCache()) !== false )
      return 'Success';
    if ( sfConfig::get('sf_web_debug', false) )
      error_log("Refreshing the cache for Manifestation's statistics (manifestation->id = ".$request->getParameter('id').")");
    
    $this->getContext()->getConfiguration()->loadHelpers('Number');
    
    $this->json = array(
      'seats' => array(
        'free' => array(
          'online' => array(
            'nb' => 0,
            'min' => array('money' => 0, 'money_txt' => ''),
            'max' => array('money' => 0, 'money_txt' => ''),
          ),
          'onsite' => array(
            'nb' => 0,
            'min' => array('money' => 0, 'money_txt' => ''),
            'max' => array('money' => 0, 'money_txt' => ''),
          ),
          'wideopen' => array(
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
          'onsite' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
          'wideopen' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
          'all' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
        ),
        'printed' => array(
          'online' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
          'onsite' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
          'wideopen' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
          'all' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
        ),
        'held' => array(
          'online' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
          'onsite' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
          'wideopen' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
          'all' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
        ),
        'closed' => array(
          'online' => array(
            'nb' => 0,
            'min' => array('money' => 0, 'money_txt' => format_currency(0, '€')),
            'max' => array('money' => 0, 'money_txt' => format_currency(0, '€')),
          ),
          'onsite' => array(
            'nb' => 0,
            'min' => array('money' => 0, 'money_txt' => format_currency(0, '€')),
            'max' => array('money' => 0, 'money_txt' => format_currency(0, '€')),
          ),
          'wideopen' => array(
            'nb' => 0,
            'min' => array('money' => 0, 'money_txt' => ''),
            'max' => array('money' => 0, 'money_txt' => ''),
          ),
          'all' => array(
            'nb' => 0,
            'min' => array('money' => 0, 'money_txt' => format_currency(0, '€')),
            'max' => array('money' => 0, 'money_txt' => format_currency(0, '€')),
          ),
        ),
      ),
      'gauges' => array(
        'free' => array(
          'online' => array(
            'nb' => 0,
            'min' => array('money' => 0, 'money_txt' => ''),
            'max' => array('money' => 0, 'money_txt' => ''),
          ),
          'onsite' => array(
            'nb' => 0,
            'min' => array('money' => 0, 'money_txt' => ''),
            'max' => array('money' => 0, 'money_txt' => ''),
          ),
          'wideopen' => array(
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
          'onsite' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
          'all' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
        ),
        'printed' => array(
          'online' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
          'onsite' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
          'all' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
        ),
        'held' => array(
          'online' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
          'onsite' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
          'all' => array('nb' => 0, 'money' => 0, 'money_txt' => ''),
        ),
        'closed' => array(
          'online' => array(
            'nb' => 0,
            'min' => array('money' => 0, 'money_txt' => format_currency(0, '€')),
            'max' => array('money' => 0, 'money_txt' => format_currency(0, '€')),
          ),
          'onsite' => array(
            'nb' => 0,
            'min' => array('money' => 0, 'money_txt' => format_currency(0, '€')),
            'max' => array('money' => 0, 'money_txt' => format_currency(0, '€')),
          ),
          'all' => array(
            'nb' => 0,
            'min' => array('money' => 0, 'money_txt' => ''),
            'max' => array('money' => 0, 'money_txt' => ''),
          ),
        ),
      ),
    );
  
  $users = array(0);
  foreach ( Doctrine::getTable('sfGuardUser')->createQuery('u')
    ->andWhereIn('u.username', sfConfig::get('app_manifestation_online_users', array('|||||||||||-|||||||||||')))
    ->select('u.id')
    ->fetchArray() as $user )
    $users[] = $user['id'];
  
  $prepare = array();
  foreach ( $users as $u )
    $prepare[] = '?';
  $prepare = '('.implode(',', $prepare).')';
  
  if ( !$request->getParameter('type', false) || $request->getParameter('type') == 'seats' )
  {
    // using directy the seats, for every seated stat... quicker and smarter
    $q = Doctrine::getTable('Seat')->createQuery('s')
      ->select ('s.id, tck.value AS value')
      ->groupBy('s.id, tck.id, tck.value')
      ->addSelect('MAX((SELECT max(ppm1.value) FROM PriceManifestation ppm1 WHERE ppm1.manifestation_id = m.id)) AS manifestation_max')
      ->addSelect('MAX((SELECT max(ppg1.value) FROM PriceGauge ppg1 WHERE ppg1.gauge_id = g.id)) AS gauge_max')
      ->addSelect('MIN((SELECT min(ppm2.value) FROM PriceManifestation ppm2 WHERE ppm2.manifestation_id = m.id)) AS manifestation_min')
      ->addSelect('MIN((SELECT min(ppg2.value) FROM PriceGauge ppg2 WHERE ppg2.gauge_id = g.id)) AS gauge_min')
      ->addSelect('(count(g.onsite) > 0) AS onsite')
      ->addSelect('(sum(tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) > 0) AS printed')
      ->addSelect('(sum(tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL) = 0 AND count(o.id) > 0) AS ordered')
      ->addSelect('(sum(h.id IS NOT NULL) > 0) AS held')
      
      ->leftJoin('s.SeatedPlan sp')
      ->leftJoin('sp.Workspaces ws')
      ->leftJoin('ws.Users wsu WITH wsu.id IN '.$prepare, $users)
      ->leftJoin('sp.Location l')
      ->leftJoin('l.Manifestations m')
      ->andWhere('m.id = ?', $request->getParameter('id'))
      ->leftJoin('m.Gauges g')
      ->andWhere('g.workspace_id = ws.id')
      ->leftJoin('m.Event e')
      ->leftJoin('e.MetaEvent me')
      
      // Holds
      ->leftJoin('s.Holds h WITH h.manifestation_id = m.id')
      
      // Related tickets
      ->leftJoin('s.Tickets tck WITH tck.manifestation_id = m.id')
      ->leftJoin('tck.Transaction t')
      ->leftJoin('t.Order o')
    ;
    $seats = new Doctrine_Collection('Seat'); // buffer to get all open seats
    
    // free online seats
    $q1 = $q->copy()
      ->leftJoin('g.PriceGauges gpg')
      ->leftJoin('gpg.Price gp')
      ->leftJoin('gp.Users gpu WITH gpu.id IN '.$prepare, $users)
      ->leftJoin('m.PriceManifestations mpm')
      ->leftJoin('mpm.Price mp')
      ->leftJoin('mp.Users mpu WITH mpu.id IN '.$prepare, $users)
      ->leftJoin('me.Users meu WITH meu.id IN '.$prepare, $users)
      ->andWhere('wsu.id IS NOT NULL AND meu.id IS NOT NULL')
      ->andWhere('gpu.id IS NOT NULL OR mpu.id IS NOT NULL')
      ->andWhere('g.online = ?', true)
    ;
    $gauges = array();
    if ( !$request->getParameter('limit', false) || $request->getParameter('limit') == 'online' )
    foreach ( $q1->execute() as $seat )
    {
      $seats[] = $seat;
      $state = 'free';
      if ( $seat->held )
        $state = 'held';
      elseif ( $seat->printed )
        $state = 'printed';
      elseif ( $seat->ordered )
        $state = 'ordered';
      
      $this->json['seats'][$state]['online']['nb']++;
      $this->json['seats'][$state]['all']['nb']++;
      if ( $state == 'free' )
      {
        $this->json['seats'][$state]['online']['min']['money'] += min(array(is_null($seat->gauge_min) ? 999999 : $seat->gauge_min, is_null($seat->manifestation_min) ? 999999 : $seat->manifestation_min));
        $this->json['seats'][$state]['online']['max']['money'] += max(array($seat->gauge_max, $seat->manifestation_max));
        $this->json['seats'][$state]['all']['min']['money']    += min(array(is_null($seat->gauge_min) ? 999999 : $seat->gauge_min, is_null($seat->manifestation_min) ? 999999 : $seat->manifestation_min));
        $this->json['seats'][$state]['all']['max']['money']    += max(array($seat->gauge_max, $seat->manifestation_max));
      }
      else
      {
        $this->json['seats'][$state]['online']['money'] += $seat->value;
        $this->json['seats'][$state]['all']['money']    += $seat->value;
      }
      
      if ( $seat->onsite )
      {
        $this->json['seats'][$state]['wideopen']['nb']++;
        if ( isset($this->json['seats'][$state]['wideopen']['min']) )
        {
          $this->json['seats'][$state]['wideopen']['min']['money'] += min(array($seat->gauge_min, $seat->manifestation_min));
          $this->json['seats'][$state]['wideopen']['max']['money'] += max(array($seat->gauge_max, $seat->manifestation_max));
        }
        else
          $this->json['seats'][$state]['wideopen']['money'] += $seat->value;
      }
    }
    foreach ( array('printed', 'ordered', 'held', 'free', 'closed') as $state )
    {
      if ( isset($this->json['seats'][$state]['online']['min']) )
      {
        $this->json['seats'][$state]['online']['min']['money_txt'] = format_currency($this->json['seats'][$state]['online']['min']['money'], '€');
        $this->json['seats'][$state]['online']['max']['money_txt'] = format_currency($this->json['seats'][$state]['online']['max']['money'], '€');
        $this->json['seats'][$state]['wideopen']['min']['money_txt'] = format_currency($this->json['seats'][$state]['wideopen']['min']['money'], '€');
        $this->json['seats'][$state]['wideopen']['max']['money_txt'] = format_currency($this->json['seats'][$state]['wideopen']['max']['money'], '€');
      }
      else
      {
        $this->json['seats'][$state]['online']['money_txt'] = format_currency($this->json['seats'][$state]['online']['money'], '€');
        $this->json['seats'][$state]['wideopen']['money_txt'] = format_currency($this->json['seats'][$state]['wideopen']['money'], '€');
      }
    }
    
    // free onsite seats
    $q2 = $q->copy()->andWhere('g.onsite = ?', true);
    if ( !$request->getParameter('limit', false) || $request->getParameter('limit') == 'onsite' )
    foreach ( $q2->execute() as $seat )
    {
      $seats[] = $seat;
      $state = 'free';
      if ( $seat->held )
        $state = 'held';
      elseif ( $seat->printed )
        $state = 'printed';
      elseif ( $seat->ordered )
        $state = 'ordered';
      
      $this->json['seats'][$state]['onsite']['nb']++;
      $this->json['seats'][$state]['all']['nb']++;
      if ( $state == 'free' )
      {
        $this->json['seats'][$state]['onsite']['min']['money'] += min(array(is_null($seat->gauge_min) ? 999999 : $seat->gauge_min, is_null($seat->manifestation_min) ? 999999 : $seat->manifestation_min));
        $this->json['seats'][$state]['onsite']['max']['money'] += max(array($seat->gauge_max, $seat->manifestation_max));
        $this->json['seats'][$state]['all']['min']['money']    += min(array(is_null($seat->gauge_min) ? 999999 : $seat->gauge_min, is_null($seat->manifestation_min) ? 999999 : $seat->manifestation_min));
        $this->json['seats'][$state]['all']['max']['money']    += max(array($seat->gauge_max, $seat->manifestation_max));
      }
      else
      {
        $this->json['seats'][$state]['onsite']['money']     += $seat->value;
        if ( isset($this->json['seats'][$state]['all']['min']) )
        {
          $this->json['seats'][$state]['all']['min']['money'] += $seat->value;
          $this->json['seats'][$state]['all']['max']['money'] += $seat->value;
        }
        else
          $this->json['seats'][$state]['all']['money'] += $seat->value;
      }
    }
    foreach ( array('printed', 'ordered', 'held', 'free', 'closed') as $state )
    {
      if ( isset($this->json['seats'][$state]['onsite']['min']) )
      {
        $this->json['seats'][$state]['onsite']['min']['money_txt'] = format_currency($this->json['seats'][$state]['onsite']['min']['money'], '€');
        $this->json['seats'][$state]['onsite']['max']['money_txt'] = format_currency($this->json['seats'][$state]['onsite']['max']['money'], '€');
      }
      else
        $this->json['seats'][$state]['onsite']['money_txt'] = format_currency($this->json['seats'][$state]['onsite']['money'], '€');
    }
    
    // closed seats -- the longer SQL query
    $q3 = $q->copy()->andWhere('g.onsite = ?', false);
    $q3
      ->leftJoin('g.PriceGauges gpg')
      ->leftJoin('gpg.Price gp')
      ->leftJoin('gp.Users gpu WITH gpu.id IN '.$prepare, $users)
      ->leftJoin('m.PriceManifestations mpm')
      ->leftJoin('mpm.Price mp')
      ->leftJoin('mp.Users mpu WITH mpu.id IN '.$prepare, $users)
      ->leftJoin('me.Users meu WITH meu.id IN '.$prepare, $users)
      ->andWhereNotIn('s.id', $seats->getPrimaryKeys())
      ->andWhere('tck.id IS NULL')
    ;
    $state = 'closed';
    if ( !$request->getParameter('limit', false) || $request->getParameter('limit') == 'closed' )
    foreach ( $q3->execute() as $seat )
    {
      $this->json['seats'][$state]['all']['nb']++;
      $this->json['seats'][$state]['all']['min']['money']    += min(array(is_null($seat->gauge_min) ? 999999 : $seat->gauge_min, is_null($seat->manifestation_min) ? 999999 : $seat->manifestation_min));
      $this->json['seats'][$state]['all']['max']['money']    += max(array($seat->gauge_max, $seat->manifestation_max));
    }
    $this->json['seats'][$state]['all']['min']['money_txt'] = format_currency($this->json['seats'][$state]['all']['min']['money'], '€');
    $this->json['seats'][$state]['all']['max']['money_txt'] = format_currency($this->json['seats'][$state]['all']['max']['money'], '€');
    
    // formatting "all" data, and removing "wideopen" seats, that has been counted twice
    foreach ( array('printed', 'ordered', 'held', 'free', 'closed') as $state )
    {
      $this->json['seats'][$state]['all']['nb'] -= $this->json['seats'][$state]['wideopen']['nb'];
      if ( isset($this->json['seats'][$state]['all']['min']) )
      {
        $this->json['seats'][$state]['all']['min']['money'] -= $this->json['seats'][$state]['wideopen']['min']['money'];
        $this->json['seats'][$state]['all']['max']['money'] -= $this->json['seats'][$state]['wideopen']['max']['money'];
        $this->json['seats'][$state]['all']['min']['money_txt'] = format_currency($this->json['seats'][$state]['all']['min']['money'], '€');
        $this->json['seats'][$state]['all']['max']['money_txt'] = format_currency($this->json['seats'][$state]['all']['max']['money'], '€');
      }
      else
      {
        $this->json['seats'][$state]['all']['money'] -= $this->json['seats'][$state]['wideopen']['money'];
        $this->json['seats'][$state]['all']['money_txt'] = format_currency($this->json['seats'][$state]['onsite']['money'], '€');
      }
    }
  }
  
  // gauges
  if ( !$request->getParameter('type', false) || $request->getParameter('type') == 'gauges' )
  {
    $q = Doctrine::getTable('Manifestation')->createQuery('m',true)
      ->andWhere('m.id = ?', $request->getParameter('id'))
      
      ->leftJoin('m.Gauges g')
      ->leftJoin('g.Tickets tck')
      ->leftJoin('tck.Seat s')
      ->leftJoin('tck.Transaction t')
      ->leftJoin('t.Order o')
      
      // Holds
      ->leftJoin('s.Holds h WITH h.manifestation_id = m.id')
      
      // problem here, duplicatas need to get the last ticket to get the seat_id, but cancellations need the first ticket...
      ->leftJoin('tck.Duplicatas d')
      ->leftJoin('tck.Cancelling c')
      
      ->addSelect('m.*, g.*, tck.*')
      ->andWhere('tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL OR o.id IS NOT NULL') // printed or integrated or booked by an order
      ->andWhere('tck.price_id IS NOT NULL') // a booked ticket is everything but a WIP
    ;
    $onsite = $q->copy()->andWhere('g.onsite = ?', true);
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
    foreach ( array('online', 'onsite', 'all') as $type )
    if ( !$request->getParameter('limit', false) || $request->getParameter('limit') == $type )
    foreach ( $$type->execute() as $manif )
    foreach ( $manif->Gauges as $gauge )
    foreach ( $gauge->Tickets as $ticket )
    {
      $state = $ticket->printed_at || $ticket->integrated_at ? 'printed' : 'ordered';
      
      // cancelling ticket
      if ( $ticket->cancelling )
        continue;
      
      // duplicatas + cancellations
      $orig = $ticket->getOriginal();
      
      // cancelled ticket
      if ( $orig->Cancelling->count() > 0 || $ticket->Cancelling->count() > 0 )
        continue;
      
      // duplicated
      if ( $orig->identifier() != $ticket->identifier() )
        continue; // only originals
      
      $this->json['gauges'][$state][$type]['nb']++;
      $this->json['gauges'][$state][$type]['money'] += $ticket->value;
    }
    
    // the "text" values with currency
    foreach ( array('online', 'onsite', 'all') as $type )
    if ( !$request->getParameter('limit', false) || $request->getParameter('limit') == $type )
    foreach ( array('printed', 'ordered', 'held',) as $state )
    foreach ( array('seats', 'gauges') as $value )
      $this->json[$value][$state][$type]['money_txt'] = format_currency($this->json[$value][$state][$type]['money'], '€');
    
    // FREE gauges
    $q = Doctrine::getTable('Gauge')->createQuery('g')
      ->andWhere('g.manifestation_id = ?', $request->getParameter('id', 0))
    ;
    // online
    if ( !$request->getParameter('limit', false) || $request->getParameter('limit') == 'online' )
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
      $min = ($gauge->value - $gauge->printed - $gauge->ordered) * $gauge->getPriceMin($users);
      $max = ($gauge->value - $gauge->printed - $gauge->ordered) * $gauge->getPriceMax($users);
      $this->json['gauges']['free']['online']['nb'] += $gauge->value - $gauge->printed - $gauge->ordered;
      $this->json['gauges']['free']['online']['min']['money'] += $min;
      $this->json['gauges']['free']['online']['max']['money'] += $max;
      
      if ( $gauge->onsite )
      {
        $this->json['gauges']['free']['wideopen']['nb'] += $gauge->value - $gauge->printed - $gauge->ordered;
        $this->json['gauges']['free']['wideopen']['min']['money'] += $min;
        $this->json['gauges']['free']['wideopen']['max']['money'] += $max;
      }
      
    }
    $this->json['gauges']['free']['online']['min']['money_txt'] = format_currency($this->json['gauges']['free']['online']['min']['money'], '€');
    $this->json['gauges']['free']['online']['max']['money_txt'] = format_currency($this->json['gauges']['free']['online']['max']['money'], '€');
    
    // onsite
    if ( !$request->getParameter('limit', false) || $request->getParameter('limit') == 'onsite' )
    foreach ( $gauges = $q->copy()
      ->andWhere('g.onsite = ?', true)
      ->execute() as $gauge )
    {
      $this->json['gauges']['free']['onsite']['nb'] += $gauge->value - $gauge->printed - $gauge->ordered;
      $this->json['gauges']['free']['onsite']['min']['money'] += ($gauge->value - $gauge->printed - $gauge->ordered) * $gauge->getPriceMin($users);
      $this->json['gauges']['free']['onsite']['max']['money'] += ($gauge->value - $gauge->printed - $gauge->ordered) * $gauge->getPriceMax($users);
    }
    $this->json['gauges']['free']['onsite']['min']['money_txt'] = format_currency($this->json['gauges']['free']['onsite']['min']['money'], '€');
    $this->json['gauges']['free']['onsite']['max']['money_txt'] = format_currency($this->json['gauges']['free']['onsite']['max']['money'], '€');

    // all
    if ( !$request->getParameter('limit', false) || $request->getParameter('limit') == 'all' )
    {
      $this->json['gauges']['free']['all']['nb'] = $this->json['gauges']['free']['onsite']['nb'] + $this->json['gauges']['free']['online']['nb'] - $this->json['gauges']['free']['wideopen']['nb'];
      $this->json['gauges']['free']['all']['min']['money'] = $this->json['gauges']['free']['onsite']['min']['money'] + $this->json['gauges']['free']['online']['min']['money'] - $this->json['gauges']['free']['wideopen']['min']['money'];
      $this->json['gauges']['free']['all']['max']['money'] = $this->json['gauges']['free']['onsite']['max']['money'] + $this->json['gauges']['free']['online']['max']['money'] - $this->json['gauges']['free']['wideopen']['max']['money'];
    }
    $this->json['gauges']['free']['all']['min']['money_txt'] = format_currency($this->json['gauges']['free']['all']['min']['money'], '€');
    $this->json['gauges']['free']['all']['max']['money_txt'] = format_currency($this->json['gauges']['free']['all']['max']['money'], '€');
  }
  
  // closed
  if ( !$request->getParameter('limit', false) || $request->getParameter('limit') == 'closed' )
  {
    $types = array();
    /*
    if ( !$request->getParameter('type', false) || $request->getParameter('type') == 'seats' )
      $types[] = 'seats';
    */
    if ( !$request->getParameter('type', false) || $request->getParameter('type') == 'gauges' )
      $types[] = 'gauges';
    
    foreach ( $types as $type )
    {
      // a trick to avoid a new complex query to the backend
      foreach ( array('nb', 'min', 'max') as $field )
      if ( is_array($this->json[$type]['closed']['all'][$field]) )
        $this->json[$type]['closed']['all'][$field]['money'] = $this->json[$type]['free']['all'][$field]['money']
          - $this->json[$type]['free']['onsite'][$field]['money']
          - $this->json[$type]['free']['online'][$field]['money']
          + $this->json[$type]['free']['wideopen'][$field]['money']
          - (isset($this->json[$type]['free']['held']) ? $this->json[$type]['held']['all']['money'] : 0)
        ;
      else
        $this->json[$type]['closed']['all'][$field] = $this->json[$type]['free']['all'][$field]
          - $this->json[$type]['free']['onsite'][$field]
          - $this->json[$type]['free']['online'][$field]
          + $this->json[$type]['free']['wideopen'][$field]
          - (isset($this->json[$type]['held']['all']) ? $this->json[$type]['held']['all']['nb'] : 0)
        ;
      
      $this->json[$type]['closed']['all']['min']['money_txt'] = format_currency($this->json[$type]['closed']['all']['min']['money'], '€');
      $this->json[$type]['closed']['all']['max']['money_txt'] = format_currency($this->json[$type]['closed']['all']['max']['money'], '€');
    }
  }
  
  if ( sfConfig::get('sf_web_debug', false) )
    error_log("Creating the cache file for Manifestation's statistics (manifestation->id = ".$request->getParameter('id').")");
  liCacher::create($path)
    ->setData($this->json)
    ->writeData();
