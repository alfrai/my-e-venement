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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    if ( !$request->getParameter('id') )
      return false;
    
    $q = Doctrine::getTable('Gauge')->createQuery('g')
      ->andWhere('g.manifestation_id = ?', $mid = $request->getParameter('id'));
    if ( $request->getParameter('wsid') == 'all' )
    {
      $gauges = $q->execute();
      $this->gauge = $gauges[0]->copy();
      $this->gauge->value = 0;
      foreach ( $gauges as $gauge )
        $this->gauge->value += $gauge->value;
    }
    else
    {
      $workspace = intval($request->getParameter('wsid')) > 0
        ? Doctrine::getTable('Workspace')->findOneById(intval($request->getParameter('wsid')))
        : $this->getUser()->getGuardUser()->Workspaces[0];
      $q->andWhere('g.workspace_id = ?', $workspace->id); // to be performed
      $gauges = $q->execute();
      $this->gauge = $gauges[0];
    }
    
    $q = new Doctrine_Query();
    $q->from('Manifestation m')
      ->leftJoin('m.Event e')
      ->leftJoin('e.MetaEvent me')
      ->leftJoin('m.Tickets t')
      ->addSelect('m.id')
      ->addSelect('sum(t.printed OR t.integrated) AS sells')
      ->addSelect('sum(NOT t.printed AND NOT t.integrated AND t.transaction_id IN (SELECT o.transaction_id FROM order o)) AS orders')
      ->addSelect('sum(NOT t.printed AND NOT t.integrated AND t.transaction_id NOT IN (SELECT o2.transaction_id FROM order o2)) AS demands')
      ->andWhere('m.id = ?',$mid)
      ->andWhere('t.duplicate IS NULL')
      ->andWhere('t.cancelling IS NULL')
      ->andWhere('t.id NOT IN (SELECT tt.cancelling FROM ticket tt WHERE cancelling IS NOT NULL)')
      ->groupBy('m.id, e.name, me.name, m.happens_at, m.duration');
    $manifs = $q->execute();
    if ( $manifs->count() > 0 )
      $this->manifestation = $manifs[0];
    
    $gauge = $this->gauge->value > 0 ? $this->gauge->value : 100;
    $this->height = array(
      'sells'   => $this->manifestation->sells / $gauge * 100,
      'orders'  => $this->manifestation->orders / $gauge * 100,
      'demands' => $this->manifestation->demands / $gauge * 100,
      'free'    => 100 - ($this->manifestation->sells+$this->manifestation->orders) / $gauge * 100
    );
    
    $this->setLayout('empty');
