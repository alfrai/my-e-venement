<?php

/**
 * get actions.
 *
 * @package    e-venement
 * @subpackage get
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class getActions extends sfActions
{
  /**
    * 
    * Returns :
    *   - HTTP status
    *     . 200: all is processed normally
    *     . 403: authentication failed
    *     . 500: internal error
    *   - content
    *     . nothing: error
    *     . json: returns a json array describing all the necessary information
    *
    **/
  public function executeInfos(sfWebRequest $request)
  {
    if ( !$request->hasParameter('debug') )
      $this->getResponse()->setContentType('application/json');
    
    $auth = new RemoteAuthenticationForm();
    $auth->bind(array('key' => $request->getParameter('key'),'ipaddress' => $request->getRemoteAddress()),array(),true);
    
    if ( !$auth->isValid() )
    {
      $this->getResponse()->setStatusCode('403');
      return $request->hasParameter('debug') ? 'Debug' : sfView::NONE;
    }
    
    $this->content = array('events' => array(), 'sites' => array());
    
    // by event
    $q = Doctrine::getTable('Manifestation')->createQuery('m')
      ->select('m.*, e.*, g.*, p.*, pm.*, l.*')
      ->addSelect('(SELECT count(t.id) FROM Ticket t WHERE t.duplicate IS NULL AND t.cancelling IS NULL AND t.id NOT IN (SELECT t.cancelling FROM ticket t2 WHERE t.cancelling IS NOT NULL) AND (t.printed OR t.integrated OR t.transaction_id IN (SELECT Order.transaction_id FROM Order))) AS nb_tickets')
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
      $this->content['locations'][$manif->location_id] = array();
      
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
          'still_have' => sfConfig::get('min_tickets') > $gauge - $manif->nb_tickets ? $gauge - $manif->nb_tickets : sfConfig::get('min_tickets'),
          'tarifs' => $tarifs,
        );
      
        $this->content['events'][$event->id] = array(
          'id' => $event->id,
          'name' => $event->name,
          'ages' => array($event->age_min, $event->age_max),
          'description' => $event->description,
          'dates' => array(
            'min' => isset($this->content['events'][$event->id]['dates']['min']) && $this->content['events'][$event->id]['dates']['min'] < $manif->happens_at
              ? $this->content['events'][$event->id]['dates']['min']
              : $manif->happens_at,
            'max' => isset($this->content['events'][$event->id]['dates']['max']) && $this->content['events'][$event->id]['dates']['max'] > $manif->happens_at
              ? $this->content['events'][$event->id]['dates']['max']
              : $manif->happens_at,
            ),
            $manif->id => $tmp,
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
        
        
        $this->content['events'][$event->id]['id'] = $event->id;
        $this->content['events'][$event->id]['name'] = $event->name;
        $this->content['events'][$event->id]['ages'] = array($event->age_min, $event->age_max);
        $this->content['events'][$event->id]['description'] = $event->description;
        $this->content['events'][$event->id]['dates'] = array(
          'min' => $this->content['events'][$event->id]['dates']['min'] < $manif->happens_at
            ? $this->content['events'][$event->id]['dates']['min']
            : $manif->happens_at,
          'max' => $this->content['events'][$event->id]['dates']['max'] > $manif->happens_at
            ? $this->content['events'][$event->id]['dates']['max']
            : $manif->happens_at,
          );
        
        $this->content['sites'][$location->id] = array(
        );
      }
    }
    
    //echo json_encode($this->content);
    return $request->hasParameter('debug') ? 'Debug' : $this->renderText(json_encode($this->content));
  }
}
