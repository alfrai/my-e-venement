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
    ->leftJoin('p.ProfessionalGroups ggp')
    ->leftJoin('ggp.Group gp ON ggp.group_id = gp.id AND gp.display_everywhere = TRUE AND (gp.sf_guard_user_id IS NULL OR gp.sf_guard_user_id = ?)', $this->getUser()->getId())
    ->leftJoin('p.Organism o')
    ->leftJoin('o.Phonenumbers opn')
    ->leftJoin('o.OrganismGroups ggo')
    ->leftJoin('ggo.Group go ON ggo.group_id = go.id AND go.display_everywhere = TRUE AND (go.sf_guard_user_id IS NULL OR go.sf_guard_user_id = ?)', $this->getUser()->getId())
    ->leftJoin('p.Contact c')
    ->leftJoin('c.ContactGroups ggc')
    ->leftJoin('ggc.Group gc ON ggc.group_id = gc.id AND gc.display_everywhere = TRUE AND (gc.sf_guard_user_id IS NULL OR gc.sf_guard_user_id = ?)', $this->getUser()->getId())
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
  
  // prices preconditions to normalization
  $total = array('total' => 0, 'prices' => array());
  $contacts = $this->prices = array();
  foreach ( $tickets as $ticket )
  if ( !isset($this->prices['price_'.$ticket->Price->id]) )
  {
    $this->total ['price_'.$ticket->Price->id] =
    $this->prices['price_'.$ticket->Price->id] = $ticket->Price->name;
  }
  
  // formatting data for normalization (prices)
  $init = array();
  foreach ( $this->prices as $id => $value )
    $init[$id] = 0;
  
  // compulsing data
  foreach ( $tickets as $ticket )
  {
    if ( !isset($contacts[$ticket->EntryElement->ContactEntry->Professional->id]) )
      $contacts[$ticket->EntryElement->ContactEntry->Professional->id] = array (
        'professional' => $ticket->EntryElement->ContactEntry->Professional,
        'tickets'      => $init,
        'manifestation'=> $ticket->EntryElement->ManifestationEntry->Manifestation,
        'total'        => 0,
      );
    
    $contacts[$ticket->EntryElement->ContactEntry->Professional->id]['tickets']['price_'.$ticket->Price->id]
      += $ticket->quantity;
    $contacts[$ticket->EntryElement->ContactEntry->Professional->id]['tickets']['total'] += $ticket->quantity;
    $total['price_'.$ticket->Price->id] += $ticket->quantity;
    $total['total'] += $ticket->quantity;
  }
  
  $this->lines = array();
  foreach ( $contacts as $contact )
  {
    // contact + pro + org's groups management
    $grps = array('contact' => array(), 'professional' => array(), 'organism' => array());
    foreach ( $contact['professional']->Contact->ContactGroups as $g )
    if ( $g->Group )
      $grps['contact'][] = (string)$g->Group;
    foreach ( $contact['professional']->ProfessionalGroups as $g )
    if ( $g->Group )
      $grps['professional'][] = (string)$g->Group;
    foreach ( $contact['professional']->Organism->OrganismGroups as $g )
    if ( $g->Group )
      $grps['organism'][] = (string)$g->Group;
    
    $opn = array();
    foreach ( $contact['professional']->Organism->Phonenumbers as $pn )
      $opn[] = (string)$pn;
    
    // real data
    $this->lines[] = array(
      'event'         => (string) $contact['manifestation']->Event,
      'date'          => (string) format_datetime($contact['manifestation']->happens_at),
      'organism'      => (string) $contact['professional']->Organism,
      'organism_an'   => (string) $contact['professional']->Organism->administrative_number,
      'organism_phones' => implode(', ',$opn),
      'organism_groups' => implode(', ',$grps['organism']),
      'contact'       => (string) $contact['professional']->Contact,
      'groups'        => implode(', ',$grps['contact']),
      'professional'  => (string) $contact['professional'],
      'professional_phonenumber' => $contact['professional']->contact_number,
      'professional_groups' => implode(', ',$grps['professional']),
      'department'    => (string) $contact['professional']->department,
      'address'       => $contact['professional']->Organism->address,
      'postalcode'    => $contact['professional']->Organism->postalcode,
      'city'          => $contact['professional']->Organism->city,
      'country'       => $contact['professional']->Organism->country,
    );
    
    $this->lines[count($this->lines)-1] = array_merge($this->lines[count($this->lines)-1],$contact['tickets']);
  }
  
  // total of totals
  $this->lines[] = $total;
  
  $params = OptionCsvForm::getDBOptions();
  $this->options = array(
    'ms' => in_array('microsoft',$params['option']),
    'tunnel' => false,
    'noheader' => false,
    'fields'   => array(
      'event',
      'date',
      'organism',
      'organism_an',
      'organism_phones',
      'organism_groups',
      'contact',
      'groups',
      'professional',
      'professional_phonenumber',
      'professional_groups',
      'department',
      //'address',
      'postalcode',
      'city',
      //'country',
    ),
  );
  foreach ( $this->prices as $id => $price )
    $this->options['fields'][] = $id;
  $this->options['fields'][] = 'total';
  
  $this->outstream = 'php://output';
  $this->delimiter = $this->options['ms'] ? ';' : ',';
  $this->enclosure = '"';
  $this->charset   = sfConfig::get('software_internals_charset');
  
  sfConfig::set('sf_escaping_strategy', false);
  $confcsv = sfConfig::get('software_internals_csv'); if ( isset($confcsv['set_charset']) && $confcsv['set_charset'] ) sfConfig::set('sf_charset', $this->options['ms'] ? $this->charset['ms'] : $this->charset['db']);
  
  if ( $request->hasParameter('debug') )
  {
    $this->getResponse()->sendHttpHeaders();
    $this->setLayout('layout');
  }
  else
    sfConfig::set('sf_web_debug', false);
