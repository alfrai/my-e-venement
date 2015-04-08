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
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    
    if ( !$request->getParameter('manifestation_id') )
      return false;
    
    $q = Doctrine::getTable('Gauge')->createQuery('g')
      //->leftJoin('g.Tickets t ON t.gauge_id = g.id AND t.duplicating IS NULL AND t.cancelling IS NULL AND t.id NOT IN (SELECT tt.cancelling FROM ticket tt WHERE tt.cancelling IS NOT NULL)')
      ->leftJoin('g.Manifestation m')
      ->leftJoin('m.ManifestationEntries me')
      ->addSelect('(SELECT sum(quantity) FROM EntryTickets et1 LEFT JOIN et1.EntryElement ee1 LEFT JOIN ee1.ContactEntry ce1 WHERE ce1.transaction_id IS NULL AND ee1.manifestation_entry_id = me.id AND ee1.accepted = true) AS accepted')
      ->addSelect('(SELECT sum(quantity) FROM EntryTickets et2 LEFT JOIN et2.EntryElement ee2 LEFT JOIN ee2.ContactEntry ce2 WHERE ce2.transaction_id IS NULL WHERE ee2.manifestation_entry_id = me.id AND ee2.accepted = false) AS refused')
      ->leftJoin('ws.GroupWorkspace gws')
      ->andWhere('me.id = ?',$request->getParameter('manifestation_id'))
      ->andWhere('gws.id IS NOT NULL');

    $gauges = $q->execute();
    
    $nb_gauge = array('demanded' => 0, 'ordered' => 0, 'sold' => 0, 'free' => 0, 'value' => 0);
    foreach ( $gauges as $gauge )
    {
      $nb_gauge['sold']     += $gauge->ordered + $gauge->printed;
      $nb_gauge['ordered']   = $gauge->accepted ? $gauge->accepted : 0;
      //$nb_gauge['demanded']  = $gauge->refused ? $gauge->refused : 0;
      $nb_gauge['value']    += $gauge->value;
    }
    $nb_gauge['free']       += $nb_gauge['value'] - $nb_gauge['ordered'] - $nb_gauge['sold'];
    $nb_gauge['value']       = $nb_gauge['value'] > 0 ? $nb_gauge['value'] : 100;
    
    $this->nb = $nb_gauge;
    $this->length = array(
      'sold'   => $nb_gauge['sold'] / $nb_gauge['value'] * 100,
      'ordered'  => $nb_gauge['ordered'] / $nb_gauge['value'] * 100,
      'demanded' => $nb_gauge['demanded'] / $nb_gauge['value'] * 100,
      'free'    => 100 - ($nb_gauge['sold']+$nb_gauge['ordered']) / $nb_gauge['value'] * 100
    );
    $this->desc = array(
      'sold'   => __('Sold or ordered through classic ticketting'),
      'ordered'  => __('Accepted (group module)'),
      'demanded' => __('Refused (group module)'),
      'free'    => __('Globally free (group module and classic ticketting merged)'),
    );
    
    $this->setLayout('empty');
