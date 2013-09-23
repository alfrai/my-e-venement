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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  $this->manifestation = $this->getRoute()->getObject();
  $this->spectators = $this->getSpectators($this->manifestation->id);
  
  $this->prices = array();
  foreach ( $this->spectators as $spectator )
  foreach ( $spectator->Tickets as $ticket )
  if ( !isset($this->prices['price_'.$ticket->price_id]) )
    $this->prices['price_'.$ticket->price_id] = $ticket->price_name;
  
  $this->lines = array();
  foreach ( $this->spectators as $spectator )
  {
    // contact infos
    $this->lines[] = array(
      'contact'     => $spectator->Contact ? (string)$spectator->Contact : '-',
      'organism'    => $spectator->Professional ? (string)$spectator->Professional->Organism : '-',
      'department'  => $spectator->Professional ? (string)$spectator->Professional->department : '-',
      'organism_at' => $spectator->Professional ? $spectator->Professional->Organism->administrative_number : '-',
    );
    
    // prices infos
    foreach ( $this->prices as $key => $name )
      $this->lines[count($this->lines)-1][$key] = 0;
    
    // tickets infos
    $total = array('qty' => 0, 'value' => 0);
    foreach ( $spectator->Tickets as $ticket )
    {
      $this->lines[count($this->lines)-1]['price_'.$ticket->price_id]++;
      $total['qty']++;
      $total['value'] += $ticket->value;
    }
    $this->lines[count($this->lines)-1]['total_qty']    = $total['qty'];
    $this->lines[count($this->lines)-1]['total_value']  = $total['value'];
    $this->lines[count($this->lines)-1]['accounting']   = $spectator->Invoice->count() > 0 ? '#'.$spectator->Invoice[0]->id : '';
    $this->lines[count($this->lines)-1]['transaction']  = '#'.$spectator->id;
  }
  
  $params = OptionCsvForm::getDBOptions();
  $this->options = array(
    'ms' => in_array('microsoft',$params['option']),
    'tunnel' => false,
    'noheader' => false,
    'fields'   => array_merge(array(
      'contact',
      'organism',
      'department',
      'organism_an',
      ),array_keys($this->prices),array(
      'total_qty',
      'total_value',
      'accounting',
      'transaction',
    )),
  );
  
  $this->outstream = 'php://output';
  $this->delimiter = $this->options['ms'] ? ';' : ',';
  $this->enclosure = '"';
  $this->charset   = sfConfig::get('software_internals_charset');

  sfConfig::set('sf_escaping_strategy', false);
  if ( $this->getContext()->getConfiguration()->getEnvironment() == 'dev' && $request->hasParameter('debug') )
  {
    $this->getResponse()->sendHttpHeaders();
    $this->setLayout('layout');
  }
  else
    sfConfig::set('sf_web_debug', false);
