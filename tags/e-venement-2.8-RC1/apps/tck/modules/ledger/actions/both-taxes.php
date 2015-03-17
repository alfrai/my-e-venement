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
      ->leftJoin('t.Users tu')
      ->leftJoin('t.Manifestations m')
      ->leftJoin('m.Gauges g')
      ->leftJoin('g.Tickets tck WITH tck.taxes != 0 AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL OR tck.cancelling IS NOT NULL) AND tck.duplicating IS NULL') // AND tck.id NOT IN (SELECT c_tck.cancelling FROM Ticket c_tck WHERE c_tck.cancelling IS NOT NULL)')
      ->leftJoin('tck.User u')
      ->orderBy('t.name')
    ;
    
    if ( isset($criterias['manifestations']) && is_array($criterias['manifestations']) && count($criterias['manifestations']) > 0 )
    {
      $cond = array();
      foreach ( $criterias['manifestations'] as $mid )
        $cond[] = '?';
      $q->andWhere('t.id = (SELECT tm.tax_id FROM TaxManifestation tm WHERE tm.manifestation_id IN ('.implode(',', $cond).'))', $criterias['manifestations']);
    }
    else
    {
      if ( isset($criterias['workspaces']) && is_array($criterias['workspaces']) && count($criterias['workspaces']) > 0 )
        $q->andWhereIn('g.workspace_id', $criterias['workspaces']);
      $q->andWhere('tck.printed_at >= ? AND tck.printed_at < ? OR tck.integrated_at >= ? AND tck.integrated_at < ?', array($dates[0], $dates[1], $dates[0], $dates[1]));
    }
    
    // restrict access to our own user
    $q = $this->restrictQueryToCurrentUser($q, 'u');
    $q = $this->restrictQueryToCurrentUser($q, 'tu');
    
    if ( isset($criterias['users']) && is_array($criterias['users']) && isset($criterias['users'][0]) && $criterias['users'][0] )
      $q->andWhereIn('tck.sf_guard_user_id',$criterias['users']);
    
    // optimizing stuff
    $q->select('t.id, t.name, t.type, t.value')
      ->addSelect('SUM(CASE WHEN tck.cancelling IS NULL THEN 1 ELSE 0 END) AS qty_in, sum(CASE WHEN tck.cancelling IS NULL THEN tck.taxes ELSE 0 END) AS amount_in')
      ->addSelect('SUM(CASE WHEN tck.cancelling IS NOT NULL THEN 1 ELSE 0 END) AS qty_out, sum(CASE WHEN tck.cancelling IS NOT NULL THEN tck.taxes ELSE 0 END) AS amount_out')
      ->groupBy('t.id, t.name, t.type, t.value')
      ->orderBy('t.type, t.value, t.name')
    ;
    $this->taxes = $q->execute();
