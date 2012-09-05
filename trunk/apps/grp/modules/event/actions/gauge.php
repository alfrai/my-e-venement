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
      ->leftJoin('g.Tickets t ON t.gauge_id = g.id AND t.duplicate IS NULL AND t.cancelling IS NULL AND t.id NOT IN (SELECT tt.cancelling FROM ticket tt WHERE tt.cancelling IS NOT NULL)')
      ->leftJoin('g.Manifestation m')
      ->leftJoin('m.ManifestationEntries me')
      ->leftJoin('me.Entries ee ON ee.manifestation_entry_id = me.id AND ee.accepted = true')
      ->leftJoin('me.Entries ee1 ON ee1.manifestation_entry_id = me.id AND ee1.accepted = false')
      ->leftJoin('ee.EntryTickets et')
      ->leftJoin('ee1.EntryTickets et1')
      ->andWhere('g.manifestation_id = ?',$request->getParameter('manifestation_id'))
      ->addSelect('g.id, m.id')
      ->addSelect('sum(t.printed OR t.integrated) + sum(NOT t.printed AND NOT t.integrated AND t.transaction_id IN (SELECT o.transaction_id FROM order o)) AS classic')
      ->addSelect('sum(et.quantity) AS validated')
      ->addSelect('sum(et1.quantity) AS refused')
      ->groupBy('g.id, m.id, g.workspace_id, g.manifestation_id, g.value, g.online, g.created_at, g.updated_at');

    $gauges = $q->execute();
    
    $nb_gauge = array('demands' => 0, 'orders' => 0, 'sells' => 0, 'free' => 0, 'value' => 0);
    foreach ( $gauges as $gauge )
    {
      $nb_gauge['sells']    += $gauge->classic;
      $nb_gauge['orders']   += $gauge->validated;
      $nb_gauge['demands']  += $gauge->refused;
      $nb_gauge['value']    += $gauge->value;
      $nb_gauge['free']     += $gauge->value - $gauge->classic - $gauge->validated;
    }
    
    $nb_gauge['value'] = $nb_gauge['value'] > 0 ? $nb_gauge['value'] : 100;
    
    $this->nb = $nb_gauge;
    $this->length = array(
      'sells'   => $nb_gauge['sells'] / $nb_gauge['value'] * 100,
      'orders'  => $nb_gauge['orders'] / $nb_gauge['value'] * 100,
      'demands' => $nb_gauge['demands'] / $nb_gauge['value'] * 100,
      'free'    => 100 - ($nb_gauge['sells']+$nb_gauge['orders']) / $nb_gauge['value'] * 100
    );
    $this->desc = array(
      'sells'   => __('Sold or ordered through classic ticketting'),
      'orders'  => __('Accepted (group module)'),
      'demands' => __('Refused (group module)'),
      'free'    => __('Globally free (group module and classic ticketting merged)'),
    );
    
    $this->setLayout('empty');
