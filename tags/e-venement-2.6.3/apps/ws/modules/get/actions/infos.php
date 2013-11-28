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
    $q = Doctrine::getTable('Gauge')->createQuery('g')
      ->select('m.*, e.*, g.*, p.*, pm.*, l.*, d.*, dg.*')
      ->leftJoin('g.Manifestation m')
      ->leftJoin('m.Event e')
      ->leftJoin('m.PriceManifestations pm')
      ->leftJoin('pm.Price p')
      ->leftJoin('m.Location l')
      ->leftJoin('p.Workspaces pw')
      ->leftJoin('m.DependsOn d')
      ->leftJoin('d.Event de')
      ->leftJoin('d.Gauge dg')
      ->leftJoin('d.PriceManifestations dpm')
      ->andWhere('m.happens_at > NOW()')
      ->andWhere('g.online')
      ->andWhere('p2.online')
      ->andWhere('g.workspace_id = pw.id')
      ->andWhere('e.display_by_default = TRUE')
      ->orderBy('e.name, m.happens_at, l.name, pm.value DESC');
    if ( sfConfig::has('app_count_demands') && sfConfig::get('app_count_demands') )
      $q->addSelect('(SELECT count(t.id) FROM Ticket t WHERE t.gauge_id = g.id AND t.duplicating IS NULL AND t.cancelling IS NULL AND t.id NOT IN (SELECT t2.cancelling FROM ticket t2 WHERE t2.cancelling IS NOT NULL)) AS nb_tickets');
    else
      $q->addSelect('(SELECT count(t.id) FROM Ticket t WHERE t.gauge_id = g.id AND t.duplicating IS NULL AND t.cancelling IS NULL AND t.id NOT IN (SELECT t2.cancelling FROM ticket t2 WHERE t2.cancelling IS NOT NULL) AND (t.printed_at IS NOT NULL OR t.integrated_at IS NOT NULL OR t.transaction_id IN (SELECT Order.transaction_id FROM Order))) AS nb_tickets');
    $gauges = $q->execute();
    
    foreach ( $gauges as $g )
    {
      $manif = $g->Manifestation;
      $event = $manif->Event;
      $location = $manif->Location;
      
      if ( !isset($this->content['events'][$event->id]) )
        $this->content['events'][$event->id] = array(
          'id'        => $event->id,
          'name'      => $event->name.(!is_null($manif->depends_on) ? ' + '.$manif->DependsOn->Event->name : ''),
          'extradesc' => $event->extradesc,
          'extraspec' => $event->extraspec,
          'ages'      => array($event->age_min, $event->age_max),
          'description' => $event->description,
          'dates'     => array(),
        );
      
      if ( !isset($this->content['events'][$manif->event_id]) )
        $this->content['events'][$manif->event_id] = array();
      if ( !isset($this->content['sites'][$manif->location_id]) )
        $this->content['sites'][$manif->location_id] = array();
      
      if ( $manif->PriceManifestations->count() > 0 )
      {
        $gauge = 0;
          
        $tarifs = array();
        foreach ( $manif->PriceManifestations as $pm )
        {
          if ( in_array($g->workspace_id,$pm->Price->getWorkspaceIds()) )
          $tarifs[$pm->Price->name] = array(
            'name' => $pm->Price->name,
            'desc' => $pm->Price->description,
            'price' => $pm->value,
          );
          
          // if this price (for this manifestation) depends on an other one... add the other one's value
          if ( !is_null($manif->depends_on) )
          foreach ( $manif->DependsOn->PriceManifestations as $dpm )
          {
            if ( $dpm->price_id == $pm->price_id )
              $tarifs[$pm->Price->name]['price'] += $dpm->value;
          }
        }
        
        $still_have = $g->value - $g->nb_tickets - $manif->online_limit > sfConfig::get('app_max_tickets')
          ? sfConfig::get('app_max_tickets')
          : ($g->value - $g->nb_tickets - $manif->online_limit > 0
              ? $g->value - $g->nb_tickets - $manif->online_limit
              : 0
            )
          ;
        $tmp = array(
          'eventid' => $event->id,
          'event' => $event->name.(!is_null($manif->depends_on) ? ' + '.$manif->DependsOn->Event->name : ''),
          'extradesc' => $event->extradesc,
          'extraspec' => $event->extraspec,
          'ages' => array($event->age_min, $event->age_max),
          'manifid' => $g->id,
          'date' => $manif->happens_at,
          'jauge' => $g->value,
          'space' => $g->Workspace->on_ticket ? (string)$g : '',
          'siteid' => $manif->location_id,
          'sitename' => $manif->Location->name,
          'siteaddr' => $manif->Location->address,
          'sitezip' => $manif->Location->postalcode,
          'sitecity' => $manif->Location->city,
          'sitecountry' => $manif->Location->country,
          'price' => $manif->PriceManifestations[0]->value,
          'still_have' => $still_have,
    	    'tarifs' => $tarifs,
        );
        
        $this->content['events'][$event->id][$g->id] = $tmp;
      
        $this->content['events'][$event->id]['dates'] = array(
          'min' => isset($this->content['events'][$event->id]['dates']['min']) && $this->content['events'][$event->id]['dates']['min'] < $manif->happens_at
            ? $this->content['events'][$event->id]['dates']['min']
            : $manif->happens_at,
          'max' => isset($this->content['events'][$event->id]['dates']['max']) && $this->content['events'][$event->id]['dates']['max'] > $manif->happens_at
            ? $this->content['events'][$event->id]['dates']['max']
            : $manif->happens_at,
          );
        
        $this->content['sites'][$location->id]['id'] = $location->id;
        $this->content['sites'][$location->id]['name'] = $location->name;
        $this->content['sites'][$location->id]['address'] = $location->address;
        $this->content['sites'][$location->id]['postal'] = $location->postalcode;
        $this->content['sites'][$location->id]['city'] = $location->city;
        $this->content['sites'][$location->id]['country'] = $location->country;
        $this->content['sites'][$location->id][$g->id] = $tmp;
      }
    }
    
    //echo json_encode($this->content);
    return $request->hasParameter('debug') ? 'Debug' : $this->renderText(wsConfiguration::formatData($this->content));

?>
