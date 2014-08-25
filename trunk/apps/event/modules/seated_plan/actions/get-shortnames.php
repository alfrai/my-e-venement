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
    $this->preLinks($request);
    if ( !$request->getParameter('gauge_id', false) )
      throw new liSeatedException('The action "get-shortnames" needs a gauge_id parameter');
    
    $q = Doctrine::getTable('Seat')->createQuery('s')
      ->andWhere('s.seated_plan_id = ?', $request->getParameter('id'))
      ->leftJoin('s.Tickets tck')
      ->andWhere('tck.gauge_id = ?', $request->getParameter('gauge_id'))
      ->leftJoin('tck.Transaction t')
      ->leftJoin('t.Contact c')
      ->leftJoin('c.Professionals p WITH p.id = t.professional_id')
      ->leftJoin('p.Organism o')
    ;
    
    $this->data = array();
    foreach ( $q->execute() as $seat )
    {
      if ( !$seat->Tickets[0]->Transaction->contact_id )
        continue;
      $contact = $seat->Tickets[0]->Transaction->Contact; // TO BE CHANGED, with named tickets
      
      $this->data[] = array(
        'type'      => 'shortname',
        'id'        => $contact->id,
        'fullname'  => (string)$contact,
        'shortname' => $contact->shortname ? $contact->shortname : strtoupper(substr($contact->name,0,1).'.'.substr($contact->firstname,0,1).'.'),
        'slug'      => $contact->slug,
        'seat_id'   => $seat->id,
        'seat_name' => $seat->name,
        'gauge_id'  => $seat->Tickets[0]->Gauge->id,
        'transaction_id' => $seat->Tickets[0]->transaction_id,
        'coordinates' => array($seat->x-$seat->diameter/2, $seat->y-$seat->diameter/2+4), // +2 is for half of the font height
        'width'     => $seat->diameter,
      );
    }
    
    if ( sfConfig::get('sf_web_debug', false) && $request->hasParameter('debug') )
      return $this->renderText(print_r($this->data));
    return 'Success';
