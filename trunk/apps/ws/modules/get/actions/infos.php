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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

    if ( !$request->hasParameter('debug') )
      $this->getResponse()->setContentType('application/json');
    
    try { $this->authenticate($request); }
    catch ( sfException $e )
    {
      $this->getResponse()->setStatusCode('403');
      return $request->hasParameter('debug') ? 'Debug' : sfView::NONE;
    }
    
    $this->content = array('events' => array(), 'sites' => array());
    
    // by event
    $q = Doctrine::getTable('Manifestation')->createQuery('m')
      ->select('m.*, e.*, g.*, p.*, pm.*, l.*')
      ->addSelect('(SELECT count(t.id) FROM Ticket t WHERE t.manifestation_id = m.id AND t.duplicate IS NULL AND t.cancelling IS NULL AND t.id NOT IN (SELECT t.cancelling FROM ticket t2 WHERE t.cancelling IS NOT NULL) AND (t.printed OR t.integrated OR t.transaction_id IN (SELECT Order.transaction_id FROM Order))) AS nb_tickets')
      ->andWhere('m.happens_at > NOW()')
      ->andWhere('g.online')
      ->andWhere('p2.online')
      ->orderBy('e.name, l.name, m.happens_at, pm.value DESC');
    $manifs = $q->execute();
    
    foreach ( $manifs as $manif )
    {
      $event = $manif->Event;
      $location = $manif->Location;
      $this->content['events'][$manif->event_id] = array();
      $this->content['sites'][$manif->location_id] = array();
      
      if ( $manif->PriceManifestations->count() > 0 )
      {
        $tarifs = array();
        foreach ( $manif->PriceManifestations as $pm )
          $tarifs[$pm->Price->name] = array(
            'name' => $pm->Price->name,
            'desc' => $pm->Price->description,
            'price' => $pm->value,
          );
        
        $gauge = 0;
        foreach ( $manif->Gauges as $g )
          $gauge += $g->value;
        
        $tmp = array(
          'eventid' => $event->id,
          'event' => $event->name,
          'ages' => array($event->age_min, $event->age_max),
          'manifid' => $manif->id,
          'date' => $manif->happens_at,
          'jauge' => $gauge,
          'siteid' => $manif->location_id,
          'sitename' => $manif->Location->name,
          'siteaddr' => $manif->Location->address,
          'sitezip' => $manif->Location->postalcode,
          'sitecity' => $manif->Location->city,
          'sitecountry' => $manif->Location->country,
          'price' => $manif->PriceManifestations[0]->value,
          'still_have' => sfConfig::get('app_min_tickets') > $gauge - $manif->nb_tickets ? ($gauge-$manif->nb_tickets > 0 ? $gauge-$manif->nb_tickets : 0) : sfConfig::get('app_min_tickets'),
          'tarifs' => $tarifs,
        );
      
        $this->content['events'][$event->id] = array(
          'id' => $event->id,
          'name' => $event->name,
          'ages' => array($event->age_min, $event->age_max),
          'description' => $event->description,
          'dates' => array(),
          $manif->id => $tmp,
        );
        $this->content['events'][$event->id]['dates'] = array(
          'min' => isset($this->content['events'][$event->id]['dates']['min']) && $this->content['events'][$event->id]['dates']['min'] < $manif->happens_at
            ? $this->content['events'][$event->id]['dates']['min']
            : $manif->happens_at,
          'max' => isset($this->content['events'][$event->id]['dates']['max']) && $this->content['events'][$event->id]['dates']['max'] > $manif->happens_at
            ? $this->content['events'][$event->id]['dates']['max']
            : $manif->happens_at,
          );
          
        $this->content['sites'][$location->id] = array(
          'id' => $location->id,
          'name' => $location->name,
          'address' => $location->address,
          'postal'  => $location->postalcode,
          'city'    => $location->city,
          'country' => $location->country,
          $manif->id => $tmp,
        );
      }
    }
    
    //echo json_encode($this->content);
    return $request->hasParameter('debug') ? 'Debug' : $this->renderText(wsConfiguration::formatData($this->content));

?>
