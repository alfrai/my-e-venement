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
    // by extra tax
    $q = Doctrine::getTable('Tax')->createQuery('t')
      ->leftJoin('t.Manifestations m')
      ->leftJoin('m.Gauges g')
      ->leftJoin('g.Workspace ws')
      ->leftJoin('t.Users u')
      ->leftJoin('t.Prices p')
      ->leftJoin('p.Tickets tck ON (tck.price_id = p.id OR tck.sf_guard_user_id = u.id OR tck.gauge_id = g.id) AND tck.taxes != 0')
      ->orderBy('t.name')
    ;
    if ( isset($criterias['manifestations']) && is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 )
      $q->andWhereIn('m.id', $criterias['manifestations']);
    else
    {
      if ( isset($criterias['workspaces']) && is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0 )
        $q->andWhereIn('ws.id', $criterias['workspaces']);
      
      $q->andWhere('tck.printed_at >= ? AND tck.printed_at < ? OR tck.integrated_at >= ? AND tck.integrated_at < ? OR tck.id IS NULL',array(
        $dates[0], $dates[1],
        $dates[0], $dates[1],
      ));
    }
    
    // restrict access to our own user
    $q = $this->restrictQueryToCurrentUser($q, 'pu');
    
    if ( isset($criterias['users']) && is_array($criterias['users']) && isset($criterias['users'][0]) && $criterias['users'][0] )
      $q->andWhereIn('tck.sf_guard_user_id',$criterias['users']);
    
    // optimizing stuff
    $q->select('t.id, t.name, t.type, t.value, count(tck.id) AS qty, sum(tck.taxes) AS amount')
      ->groupBy('t.id, t.name, t.type, t.value')
      ->orderBy('count(tck.id) DESC, t.name, t.type, t.value');
    $this->taxes = $q->execute();
