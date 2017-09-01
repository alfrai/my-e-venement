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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

/**
 * seats actions.
 *
 * @package    e-venement
 * @subpackage seats
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class seatsActions extends sfActions
{
  public function preExecute()
  {
    $this->dispatcher->notify(new sfEvent($this, 'pub.pre_execute', array('configuration' => $this->configuration)));
    parent::preExecute();
  }

 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $ids = array();
    if ( $request->getParameter('gauge_id') ) {
        $ids[] = $request->getParameter('gauge_id');
    }
    if ( $request->getParameter('gauges_list', array()) && is_array($request->getParameter('gauges_list')) ) {
        foreach ( $request->getParameter('gauges_list') as $id ) {
            $ids[] = $id;
        }
    }
    if ( ($zid = intval($request->getParameter('from_zone', false))) > 0 ) {
        $q = Doctrine::getTable('Gauge')->createQuery('g')
            ->select('g.id')
            ->leftJoin('ws.SeatedPlans sp')
            ->leftJoin('sp.Zones z')
            ->andWhere('z.id = ?', $zid)
        ;
        $zids = $q->execute()->getPrimaryKeys();
        foreach ( $ids as $i => $id ) {
            if ( !in_array($id, $zids) ) {
                unset($ids[$i]);
            }
        }
    }
    $this->forward404Unless($ids);
    
    // basic data
    $q = Doctrine::getTable('SeatedPlan')->createQuery('sp')
      ->leftJoin('sp.Seats s')
      ->leftJoin('sp.Zones z')
      ->andWhere($request->hasParameter('from_zone') ? 'z.id IS NOT NULL' : 'z.id IS NULL')
      ->orderBy('s.name')
      
      ->leftJoin('sp.Workspaces ws')
      ->leftJoin('ws.Gauges g')
      ->andWhereIn('g.id', $ids)
      ->andWhere('g.online = ?', true)
      ->andWhereIn('g.workspace_id', array_keys($this->getUser()->getWorkspacesCredentials()))
      
      ->leftJoin('g.Manifestation m')
      ->leftJoin('m.Event e')
      ->andWhereIn('e.meta_event_id', array_keys($this->getUser()->getMetaEventsCredentials()))
      
      ->andWhere('sp.location_id = m.location_id')
      
      ->select('sp.*, ws.*, g.*, m.*, l.*')
    ;
    if ( $request->getParameter('id') )
      $q->andWhere('sp.id = ?', $request->getParameter('id'));
    $this->seated_plans = $q->execute();
    $this->forward404Unless($this->seated_plans);
    
    // specific data
    $this->occupied = array();
    $this->transaction = $this->getUser()->getTransaction();
    
    if ( $request->hasParameter('debug') && sfConfig::get('sf_web_debug', false) )
    {
      $this->debug = true;
      $this->getResponse()->setContentType('text/html');
      $this->getResponse()->sendHttpHeaders();
      $this->setLayout(sfConfig::get('app_options_template', 'public'));
    }
    else
      sfConfig::set('sf_web_debug', false);
    sfConfig::set('sf_escaping_strategy', false);
    
    $spids = array(0);
    foreach ( $this->seated_plans as $sp )
      $spids[] = $sp->id;
    
    $q = Doctrine::getTable('Ticket')->createQuery('tck')
      ->select('tck.*, t.*, s.*, sp.*')
      ->leftJoin('tck.Seat s')
      ->leftJoin('tck.Transaction t')
      ->leftJoin('t.Contact c')
      ->leftJoin('t.Professional pro')
      ->leftJoin('pro.Organism org')
      ->leftJoin('pro.Contact pc')
      ->leftJoin('t.Order o')
      ->leftJoin('s.SeatedPlan sp')
      ->andWhere('tck.seat_id IS NOT NULL')
      ->andWhereIn('sp.id', $spids)
      ->leftJoin('tck.Manifestation m')
      ->leftJoin('m.Gauge g')
      ->andWhereIn('g.id', $ids);
    ;
    
    foreach ( $tickets = $q->execute() as $ticket )
      $this->occupied[$ticket->seat_id] = array(
        'type'            => 'ordered'.($ticket->transaction_id === $this->transaction->id ? ' in-progress' : ''),
        'transaction_id'  => in_array($ticket->gauge_id, $ids) && $ticket->transaction_id === $this->transaction->id ? '#'.$this->transaction->id : false,
        'ticket_id'       => $ticket->id,
        'price_id'        => $ticket->price_id,
        'gauge_id'        => $ticket->gauge_id,
        //'spectator'      => $ticket->Transaction->professional_id ? $ticket->Transaction->Professional->Contact.' '.$ticket->Transaction->Professional : (string)$ticket->Transaction->Contact,
      );
    
    // Holds...
    $q = Doctrine::getTable('HoldContent')->createQuery('hc')
      ->select('hc.*')
      ->leftJoin('hc.Hold h')
      ->leftJoin('h.Manifestation m')
      ->leftJoin('m.Gauges g')
      ->andWhereIn('g.id', $ids)
    ;
    $arr = array();
    foreach ( $q->execute() as $hc )
    if ( !isset($this->occupied[$hc->seat_id]) )
      $this->occupied[$hc->seat_id] = array(
        'type'            => 'hold',
        'transaction_id'  => false,
        'hold_id'         => $hc->hold_id,
      );
    
  }
  
  public function executeGetZones(sfWebRequest $request)
  {
    $this->data = array(
        'type' => 'zones',
        'zones' => array(),
    );
    
    // data validation
    $ids = json_decode($request->getParameter('gauge_ids'), true);
    if ( !is_array($ids) ) {
        return 'Success';
    }
    $except = $request->getParameter('except', [0]);
    if ( !is_array($except) ) {
        $except = [0];
    }
    $this->gcache = new Doctrine_Collection('Gauge');
    $this->colors = $this->getZoneColors();
    
    // the map of the zone
    $q = Doctrine::getTable('SeatedPlan')->createQuery('sp')
        ->leftJoin('sp.Zones z')
        ->andWhereIn('z.id', $except)
        ->select('sp.id')
    ;
    $spids = $q->execute()->getPrimaryKeys();
    $q = Doctrine::getTable('SeatedPlanZone')->createQuery('z')
        ->select('z.*')
        ->leftJoin('z.SeatedPlan sp')
        
        ->leftJoin('sp.Workspaces ws')
        ->leftJoin('ws.Gauges g')
        ->andWhereIn('g.id', $ids)
        
        ->leftJoin('g.Manifestation m')
        ->andWhere('sp.location_id = m.location_id')
        
        ->andWhereNotIn('sp.id', $spids)
    ;
    $zones = $q->fetchArray();
    foreach ( $zones as $zone ) {
        $this->data['zones'][$zone['id']] = array(
            'id'                => $zone['id'],
            'seated_plan_id'    => $zone['seated_plan_id'],
            'polygon'           => json_decode($zone['zone'], true),
            'color'             => 'purple' // default color
        );
        
        // the state of the zone: calculating ratio between the size of the gauge and its free space
        $this->setZoneColor($zone, $ids);
    }
  }
  
  private function getCurrentGauges($seated_plan_id, $gauge_ids)
  {
    // trying to use cache of gauges to get back their state
    if ( !isset($this->gcache[$seated_plan_id]) ) {
        $q = Doctrine::getTable('Gauge')->createQuery('g')
                ->andWhereIn('g.id', $gauge_ids)
            ->leftJoin('ws.SeatedPlans sp')
            ->andWhere('sp.id = ?', $seated_plan_id)
        ;
        $gauges = $q->execute();
        $this->gcache[$seated_plan_id] = $gauges;
    }
    
    return $this->gcache[$seated_plan_id];
  }
  
  private function setZoneColor($zone, $gauge_ids)
  {
    $gauges = $this->getCurrentGauges($zone['seated_plan_id'], $gauge_ids);
    
    $state = array('total' => 0, 'free' => 0);
    foreach ( $gauges as $gauge ) {
        $state['free']  += $gauge->free;
        $state['total'] += $gauge->value;
    }
    $ratio = $state['free']/$state['total'];
    
    // setting the good color representing the state of the gauge
    if ( $ratio > 0.8 ) {
        $this->data['zones'][$zone['id']]['color'] = $this->colors['free'];
    }
    elseif ( $ratio > 0.6 ) {
        $this->data['zones'][$zone['id']]['color'] = $this->colors['partially-free'];
    }
    elseif ( $ratio > 0.4 ) {
        $this->data['zones'][$zone['id']]['color'] = $this->colors['middlestate'];
    }
    elseif ( $ratio > 0.2 ) {
        $this->data['zones'][$zone['id']]['color'] = $this->colors['partially-booked'];
    }
    elseif ( $ratio > 0 ) {
        $this->data['zones'][$zone['id']]['color'] = $this->colors['mostly-booked'];
    }
    else {
        $this->data['zones'][$zone['id']]['color'] = $this->colors['booked'];
    }
  }
  
  private function getZoneColors()
  {
    $props = $colors = array();
    
    foreach ( array('css/pub-seated-plan-masscolors.css', 'private/pub-seated-plan-masscolors.css') as $file ) {
        $colors = array_merge($colors, $this->parseCSS($file));
    }
    
    return $colors;
  }
  
  private function parseCSS($file)
  {
    if ( !is_readable($file) ) {
        return array();
    }
    
    $colors = array();
    $lines = explode("\n", file_get_contents(sfConfig::get('sf_web_dir').'/css/pub-seated-plan-masscolors.css'));
    
    foreach ( $lines as $line ) {
        preg_match('/zone\.([\w\.#-_]+)\s*{\s*background-color:\s*([\w-_#\d]+);?\s*}/', $line, $matches);
        $props[] = $matches;
    }
    
    foreach ( $props as $prop ) {
        if ( count($prop) != 3 ) {
            continue;
        }
        
        $colors[$prop[1]] = $prop[2];
    }
    
    foreach ( array('free', 'partially-free', 'middlestate', 'partially-booked', 'mostly-booked', 'booked') as $color ) {
        if ( isset($colors[$color]) ) {
            continue;
        }
        $colors[$color] = 'purple';
    }
    
    return $colors;
  }
}
