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
    $mids = array();
    $this->transaction = NULL;
    
    if ( isset($values['id']) || $request->getParameter('id') > 0 )
    {
      $this->transaction = Doctrine::getTable('Transaction')
        ->findOneById($values['id'] ? $values['id'] : $request->getParameter('id'));
      foreach ( $this->transaction->Tickets as $ticket )
        $mids[$ticket->Manifestation->id] = $ticket->Manifestation->id;
    }
    
    if ( $request->getParameter('manif_new') )
    {
      $eids = array('0');
      $mid = false;
      if ( substr($request->getParameter('manif_new'),0,7) == '#manif-' )
      {
        $mid = array();
        $manifs = explode(',',$request->getParameter('manif_new'));
        foreach ( $manifs as $manif )
          $mid[] = substr($manif,7);
      }
      else
      {
        $charset = sfConfig::get('software_internals_charset');
        $q = Doctrine::getTable('Event')->createQuery('e')
          ->select('e.id')
          ->limit(intval($request->getParameter('limit')) > 0 ? intval($request->getParameter('limit')) : (isset($config['max_display']) ? $config['max_display'] : 30));
        if ( $request->getParameter('display_all','false') !== 'true' )
          $q->andWhere('e.display_by_default = TRUE');

        $q = Doctrine_Core::getTable('Event')->search(
          strtolower(iconv($charset['db'],$charset['ascii'],$request->getParameter('manif_new'))).'*',$q);
        foreach ( $q->fetchArray() as $event )
          $eids[] = $event['id'];
      }
      
      // prices to be shown for each manifestations
      $q = Doctrine::getTable('Manifestation')->createQuery('m')
        ->leftJoin('m.Color color')
        ->andWhereNotIn('m.id',$mids)
        ->select('m.*, e.*, color.*, l.*, pm.*, p.*, g.*, me.*, w.*, pu.*, wu.*, meu.*')
        ->orderBy('happens_at ASC')
        ->limit(intval($request->getParameter('limit')) > 0 ? intval($request->getParameter('limit')) : (isset($config['max_display']) ? $config['max_display'] : 30));
      
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
      $config =  sfConfig::get('app_transaction_manifs',array());
      $eids = array();
      $q = Doctrine::getTable('Manifestation')
        ->createQuery('m',true)
        ->leftJoin('m.Color color')
        ->leftJoin('m.Gauges g')
        ->leftJoin('g.Workspace gw')
        ->leftJoin('gw.Order gwo ON gwo.workspace_id = gw.id AND gwo.sf_guard_user_id = '.intval($this->getUser()->getId()))
        ->leftJoin('m.Prices p')
        ->leftJoin('p.PriceManifestations pm ON p.id = pm.price_id AND m.id = pm.manifestation_id')
        ->leftJoin('m.PriceManifestations mp ON p.id = mp.price_id AND m.id = mp.manifestation_id')
        ->leftJoin('p.Users u')
        ->leftJoin('p.Workspaces pw')
        ->leftJoin('pw.Gauges pg ON pw.id = pg.workspace_id AND pg.manifestation_id = m.id')
        ->andWhere('pg.id = g.id')
        ->andWhereNotIn('m.id',$mids)
        ->andWhere('u.id = ?',$this->getUser()->getId())
        ->andWhere('e.display_by_default')
        ->orderBy('m.happens_at, e.name')
        ->limit(intval($request->getParameter('limit')) > 0 ? intval($request->getParameter('limit')) : (isset($config['max_display']) ? $config['max_display'] : 10));

      $this->page = intval($request->getParameter('page',0));
      
      if ( !$this->getUser()->hasCredential('tck-unblock') || $this->page >= 0 )
        $q->andWhere('happens_at >= ?',date('Y-m-d'));
      elseif ( $this->page < 0 )
        $q->andWhere('happens_at < ?',date('Y-m-d'))
          ->orderBy('m.happens_at DESC, e.name');

      $q->offset(($this->page < 0 ? -$this->page - 1 : $this->page)*(isset($config['max_display']) ? $config['max_display'] : 10));
      
      $this->manifestations_add = $q->execute();
    }
