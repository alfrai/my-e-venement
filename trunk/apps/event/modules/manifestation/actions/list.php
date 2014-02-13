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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $this->location_id    = $request->getParameter('location_id');
    $this->event_id       = $request->getParameter('event_id');
    $this->only_blocking  = $request->hasParameter('only_blocking');
    $this->only_pending   = $request->hasParameter('only_pending');
    
    $this->from = date('Y-m-d H:i:00', $request->getParameter('start',$time = time()));
    $this->to = date('Y-m-d H:i:00', $request->getParameter('end',strtotime('+ 1 month', $time)));
    $this->month_view = strtotime($this->to) - strtotime($this->from) >= strtotime('+ 1 month',$time) - $time;
    
    $no_ids = $request->getParameter('no_ids',array());
    if ( !is_array($no_ids) ) $no_ids = array();
    foreach ( $no_ids as $key => $value )
    if ( !$value )
      unset($no_ids[$key]);
    
    $end = "m.reservation_ends_at"; // + (m.duration||' seconds')::interval";
    $q = Doctrine::getTable('Manifestation')->createQuery('m')
      ->select('m.*, l.*, mb.*, me.*, c.*, e.*, g.*')
      ->leftJoin('m.Color c')
      ->leftJoin('m.Booking mb')
      ->andWhere('(TRUE')
      ->andWhere("m.reservation_begins_at >= ? AND m.reservation_begins_at < ?", array($this->from, $this->to))
      ->orWhere("$end > ? AND $end <= ?", array($this->from, $this->to))
      ->orWhere("m.reservation_begins_at < ? AND $end > ?", array($this->from, $this->to))
      ->andWhere('TRUE)')
      ->orderBy('m.happens_at DESC');
    if ( $this->location_id )
      $q->andWhere('(TRUE')
        ->andWhere('m.location_id = ?',$this->location_id)
        ->leftJoin('m.Booking b')
        ->orWhere('b.id = ?',$this->location_id)
        ->andWhere('TRUE)');
    if ( $this->only_blocking )
      $q->andWhere('m.blocking = TRUE');
    if ( $this->only_pending )
      $q->andWhere('m.reservation_confirmed = FALSE');
    if ( $this->event_id )
      $q->andWhere('m.event_id = ?', $this->event_id);
    elseif ( $this->month_view )
    {
      // if the manifestation's duration > 1 day or the manifestation's reservation starts one day and stops another and duration > 18h
      $q->andWhere("(me.hide_in_month_calendars = FALSE OR DATE_TRUNC('day', m.reservation_begins_at) + '1 day'::interval < DATE_TRUNC('day', m.reservation_ends_at) AND m.duration > ?)", array(
        18*60*60, // starts and stops in differents days, and length > 18h
      ));
    }
    if ( $no_ids )
      $q->andWhereNotIn('m.id',$no_ids);
    // security filtering
    EventFormFilter::addCredentialsQueryPart($q);
    
    // event filters
    $data = $this->getUser()->getAttribute('event.filters', array(), 'admin_module');
    if ( $data )
    {
      $filters = new EventFormFilter;
      $data[$filters->getCSRFFieldName()] = $filters->getCSRFToken();
      $filters->bind($data);
      if ( $filters->isValid() )
      {
        $query = $filters->getQuery($data);
        $a = $query->getRootAlias();
        if ( !$query->contains("LEFT JOIN $a.Manifestations m") )
        $query->leftJoin("$a.Manifestations m");
        
        $manifs = array();
        foreach ( $query->select("$a.id, m.id, m.event_id")->andWhere('m.id IS NOT NULL')->execute() as $event )
        foreach ( $event->Manifestations as $manif )
          $manifs[] = $manif->id;
        
        // add filters into the "mother" query;
        $q->andWhereIn('m.id',$manifs);
      }
      else error_log('Cannot apply event filters to calendar ('.$filters->getErrorSchema().').');
    }
    
    // for the "global locations usage (free/busy)" display
    if ( $request->hasParameter('fblocation') )
    {
      $q->orderBy('m.happens_at ASC') // very important !!
        ->andWhere('m.reservation_confirmed = TRUE');
      //$this->locations = Doctrine::getTable('Location')->createQuery('l')->andWhere('place = TRUE')->orderBy('name')->execute();
      //$this->setTemplate('listFbLocation');
    }
    
    $this->manifestations = $q->execute();
    $this->forward404Unless($this->manifestations);
    
    // the configuration
    $options = sfConfig::get('app_manifestation_reservations', array());
    $this->display_reservations =
         isset($options['enable']) && $options['enable']
      && isset($options['shown_in_calendar']) && $options['shown_in_calendar'];
    
    $this->debug = false;
    if ( $request->hasParameter('debug') )
    {
      $this->getResponse()->setContentType('text/html');
      $this->setLayout('layout');
      $this->debug = true;
    }
    else
      sfConfig::set('sf_escaping_strategy', false);
