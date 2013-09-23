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
    // by price
    $q = Doctrine::getTable('Price')->createQuery('p')
      ->leftJoin('p.Tickets t')
      ->leftJoin('t.Gauge g')
      ->leftJoin('t.User u')
      ->andWhere('t.printed_at IS NOT NULL OR t.cancelling IS NOT NULL OR t.integrated_at IS NOT NULL')
      ->andWhere('t.duplicating IS NULL') // get only originals
      ->orderBy('p.name');
    if ( isset($criterias['users']) && is_array($criterias['users']) && $criterias['users'][0] )
      $q->andWhereIn('t.sf_guard_user_id',$criterias['users']);
    if ( isset($criterias['workspaces']) && is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0 )
      $q->andWhereIn('g.workspace_id',$criterias['workspaces']);
    if ( isset($criterias['manifestations']) && is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 )
      $q->andWhereIn('t.manifestation_id',$criterias['manifestations']);
    else
      $q->andWhere('t.cancelling IS NULL AND (t.printed_at IS NOT NULL AND t.printed_at >= ? AND t.printed_at < ? OR t.integrated_at IS NOT NULL AND t.integrated_at >= ? AND t.integrated_at < ?) OR t.cancelling IS NOT NULL AND t.created_at >= ? AND t.created_at < ?',array(
          $dates[0], $dates[1],
          $dates[0], $dates[1],
          $dates[0], $dates[1],
        ));

    // restrict access to our own user
    $q = $this->restrictQueryToCurrentUser($q);
    
    // optimizing stuff
    $q->select('p.id, p.name, p.description')
      ->addSelect('sum(t.value * CASE WHEN t.cancelling IS NOT NULL THEN 1 ELSE 0 END) AS tickets_cancelling_value')
      ->addSelect('sum(t.value * CASE WHEN t.cancelling IS     NULL THEN 1 ELSE 0 END) AS tickets_normal_value')
      ->addSelect('sum(t.cancelling IS NOT NULL) AS nb_cancelling')
      ->addSelect('count(t.id) AS nb_tickets')
      ->groupBy('p.id, p.name, p.description')
      ->orderBy('p.name');
    $this->byPrice = $q->execute();
