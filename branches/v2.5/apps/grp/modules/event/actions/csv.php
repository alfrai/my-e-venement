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
*    Copyright (c) 2006-2012 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2012 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  $this->getContext()->getConfiguration()->loadHelpers('Date');
  $event = $this->getRoute()->getObject();
  
  $q = Doctrine::getTable('EntryTickets')->createQuery('et')
    ->leftJoin('et.EntryElement ee')
    ->leftJoin('ee.ContactEntry ce')
    ->leftJoin('ce.Professional p')
    ->leftJoin('p.Organism o')
    ->leftJoin('p.Contact c')
    ->leftJoin('ee.ManifestationEntry me')
    ->leftJoin('me.Manifestation m')
    ->leftJoin('m.Event e')
    ->leftJoin('et.Price pr')
    ->andWhere('m.event_id = ?',$event->id)
    ->orderBy('o.name, c.name, pr.name');
  if ( ($meid = intval($request->getParameter('manifestation_id'))) > 0 )
    $q->andWhere('me.id = ?',$meid);
  switch ( $request->getParameter('type') ) {
  case 'refused':
    $q->andWhere('ee.accepted = false');
    break;
  case 'accepted':
    $q->andWhere('ee.accepted = true');
    break;
  default:
    break;
  }
  
  $tickets = $q->execute();
  
  $contacts = $this->prices = array();
  foreach ( $tickets as $ticket )
  if ( !isset($this->prices['price_'.$ticket->Price->id]) )
    $this->prices['price_'.$ticket->Price->id] = $ticket->Price->name;
  
  $init = array();
  foreach ( $this->prices as $id => $value )
    $init[$id] = 0;
  
  foreach ( $tickets as $ticket )
  {
    if ( !isset($contacts[$ticket->EntryElement->ContactEntry->Professional->id]) )
      $contacts[$ticket->EntryElement->ContactEntry->Professional->id] = array (
        'professional' => $ticket->EntryElement->ContactEntry->Professional,
        'tickets'      => $init,
        'manifestation'=> $ticket->EntryElement->ManifestationEntry->Manifestation,
      );
    
    $contacts[$ticket->EntryElement->ContactEntry->Professional->id]['tickets']['price_'.$ticket->Price->id]
      += $ticket->quantity;
  }
  
  $this->lines = array();
  foreach ( $contacts as $contact )
  {
    $this->lines[] = array(
      'event'         => (string) $contact['manifestation']->Event,
      'date'          => (string) format_datetime($contact['manifestation']->happens_at),
      'organism'      => (string) $contact['professional']->Organism,
      'contact'       => (string) $contact['professional']->Contact,
      'professional'  => (string) $contact['professional'],
      'address'       => $contact['professional']->Organism->address,
      'postalcode'    => $contact['professional']->Organism->postalcode,
      'city'          => $contact['professional']->Organism->city,
      'country'       => $contact['professional']->Organism->country,
    );
    
    $this->lines[count($this->lines)-1] = array_merge($this->lines[count($this->lines)-1],$contact['tickets']);
  }
  
  $params = OptionCsvForm::getDBOptions();
  $this->options = array(
    'ms' => in_array('microsoft',$params['option']),
    'tunnel' => false,
    'noheader' => false,
    'fields'   => array(
      'event',
      'date',
      'organism',
      'contact',
      'professional',
      //'address',
      'postalcode',
      'city',
      //'country',
    ),
  );
  foreach ( $this->prices as $id => $price )
    $this->options['fields'][] = $id;
  
  $this->outstream = 'php://output';
  $this->delimiter = $this->options['ms'] ? ';' : ',';
  $this->enclosure = '"';
  $this->charset   = sfConfig::get('software_internals_charset');
  
  sfConfig::set('sf_escaping_strategy', false);
  sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
  
  if ( $request->hasParameter('debug') )
  {
    $this->getResponse()->sendHttpHeaders();
    $this->setLayout('layout');
  }
  else
    sfConfig::set('sf_web_debug', false);

