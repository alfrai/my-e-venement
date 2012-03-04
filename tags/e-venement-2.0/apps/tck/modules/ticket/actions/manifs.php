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
    sfContext::getInstance()->getConfiguration()->loadHelpers('CrossAppLink');
    $values = $request->getParameter('transaction');
    $this->transaction = Doctrine::getTable('Transaction')
      ->findOneById($values['id'] ? $values['id'] : $request->getParameter('id'));
    
    $mids = array();
    foreach ( $this->transaction->Tickets as $ticket )
      $mids[$ticket->Manifestation->id] = $ticket->Manifestation->id;
    
    if ( $request->getParameter('manif_new') )
    {
      $eids = array('0');
      $mid = false;
      if ( substr($request->getParameter('manif_new'),0,7) == '#manif-' )
      {
        $mid = array();
        $manifs = split(',',$request->getParameter('manif_new'));
        foreach ( $manifs as $manif )
          $mid[] = substr($manif,7);
      }
      else
      {
        foreach ( Doctrine::getTable('Event')->search(
          strtolower($request->getParameter('manif_new')).'*') as $id )
          $eids[] = $id['id'];
      }
      
      // prices to be shown for each manifestations
      $q = Doctrine::getTable('Manifestation')->createQuery('m')
        ->leftJoin('m.Color color')
        ->andWhereNotIn('m.id',$mids)
        ->orderBy('happens_at ASC');
      
      if ( !$mid )
        $q->andWhereIn('e.id',$eids);
      else
        $q->andWhereIn('m.id',$mid);
      
      if ( !$this->getUser()->hasCredential('tck-unblock') )
        $q->andWhere('happens_at >= ?',date('Y-m-d'));
      
      $this->manifestations_add = $q->execute();
    }
    else
    {
      $eids = array();
      $q = Doctrine::getTable('Manifestation')
        ->createQuery('m',true)
        ->leftJoin('m.Color color')
        ->leftJoin('m.Gauges g')
        ->leftJoin('m.Prices p')
        ->leftJoin('p.Workspaces pw')
        ->leftJoin('pw.Gauges pg ON pw.id = pg.workspace_id AND pg.manifestation_id = m.id')
        ->andWhere('pg.id = g.id')
        ->andWhereNotIn('m.id',$mids)
        ->orderBy('m.happens_at, e.name')
        ->limit(($config = sfConfig::get('app_transaction_manifs')) ? $config['max_display'] : 10);
      //if ( !$this->getUser()->hasCredential('tck-unblock') )
        $q->andWhere('happens_at >= ?',date('Y-m-d'));
      $this->manifestations_add = $q->execute();
    }
