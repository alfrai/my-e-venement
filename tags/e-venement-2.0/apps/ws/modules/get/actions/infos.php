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
      ->leftJoin('p.Workspaces pw')
      ->addSelect('(SELECT count(t.id) FROM Ticket t WHERE t.manifestation_id = m.id AND t.duplicate IS NULL AND t.cancelling IS NULL AND t.id NOT IN (SELECT t.cancelling FROM ticket t2 WHERE t.cancelling IS NOT NULL) AND (t.printed OR t.integrated OR t.transaction_id IN (SELECT Order.transaction_id FROM Order))) AS nb_tickets')
      ->andWhere('m.happens_at > NOW()')
      ->andWhere('g.online')
      ->andWhere('p2.online')
      ->andWhere('g.workspace_id = pw.id')
      ->orderBy('e.name, l.name, m.happens_at, pm.value DESC');
    $manifs = $q->execute();
    
    foreach ( $manifs as $manif )
    {
      $event = $manif->Event;
      $location = $manif->Location;
      if ( !isset($this->content['events'][$manif->event_id]) )
        $this->content['events'][$manif->event_id] = array();
      if ( !isset($this->content['sites'][$manif->location_id]) )
        $this->content['sites'][$manif->location_id] = array();
      
      if ( $manif->PriceManifestations->count() > 0 )
      {
        $this->content['events'][$event->id]['id'] = $event->id;
        $this->content['events'][$event->id]['name'] = $event->name;
        $this->content['events'][$event->id]['ages'] = array($event->age_min, $event->age_max);
        $this->content['events'][$event->id]['description'] = $event->description;
        $this->content['events'][$event->id]['dates'] = array();
        
        $gauge = 0;
        foreach ( $manif->Gauges as $g )
        {
          //$gauge += $g->value;
          
          $tarifs = array();
          foreach ( $manif->PriceManifestations as $pm )
          {
            if ( in_array($g->workspace_id,$pm->Price->getWorkspaceIds()) )
            $tarifs[$pm->Price->name] = array(
              'name' => $pm->Price->name,
              'desc' => $pm->Price->description,
              'price' => $pm->value,
            );
          }
          
          $tmp = array(
            'eventid' => $event->id,
            'event' => $event->name,
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
            'still_have' => $manif->online_limit > $g->value - $manif->nb_tickets ? ($g->value-$manif->nb_tickets > 0 ? $g->value-$manif->nb_tickets : 0) : sfConfig::get('app_max_tickets'),
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
    }
    
    //echo json_encode($this->content);
    return $request->hasParameter('debug') ? 'Debug' : $this->renderText(wsConfiguration::formatData($this->content));

?>
