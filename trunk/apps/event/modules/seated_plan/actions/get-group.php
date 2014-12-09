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
    $this->preLinks($request);
    if ( !$request->getParameter('gauge_id', false) && !$request->getParameter('gauges_list',false) )
      throw new liSeatedException('The action "get-groups" needs a gauge_id or 1+ gauges_list[] parameter');
    if ( !$request->getParameter('group_id', false) )
      throw new liSeatedException('The action "get-groups" needs a group_id parameter');
    
    $ids = array();
    if ( is_array($request->getParameter('gauges_list',false)) )
    foreach ( $request->getParameter('gauges_list') as $id )
      $ids[] = $id;
    if ( $request->getParameter('gauge_id', false) )
      $ids[] = $request->getParameter('gauge_id', false);
    
    $q = Doctrine::getTable('Seat')->createQuery('s')
      ->leftJoin('s.Tickets tck')
      ->andWhereIn('tck.gauge_id', $ids)
      ->leftJoin('tck.Transaction t')
      ->leftJoin('tck.DirectContact dc')
      ->leftJoin('dc.Groups dcg')
      ->leftJoin('t.Contact c')
      ->leftJoin('c.Groups cg')
      ->leftJoin('t.Professional p')
      ->leftJoin('p.Groups pg')
      ->andWhere('dcg.id = ? OR cg.id = ? OR pg.id = ?', array($request->getParameter('group_id'),$request->getParameter('group_id'),$request->getParameter('group_id')))
    ;
    if ( count($ids) == 1 )
      $q->andWhere('s.seated_plan_id = ?', $request->getParameter('id'));
    
    $this->data = array();
    foreach ( $q->execute() as $seat )
    {
      $group = $seat->Tickets[0]->contact_id && $seat->Tickets[0]->DirectContact->Groups->count() > 0
        ? $seat->Tickets[0]->DirectContact->Groups[0]
        : ($seat->Tickets[0]->Transaction->Contact->Groups->count() > 0
        ? $seat->Tickets[0]->Transaction->Contact->Groups[0]
        : $seat->Tickets[0]->Transaction->Professional->Groups[0])
      ;
      
      $this->data[] = array(
        'type'      => 'group',
        'seat_id'   => $seat->id,
        'seat_name' => $seat->name,
        'seat_class'=> $seat->class,
        'group_id'  => $group->id,
        'group_name'=> (string)$group,
        'gauge_id'  => $seat->Tickets[0]->Gauge->id,
        'transaction_id' => $seat->Tickets[0]->transaction_id,
        'coordinates' => array($seat->x-$seat->diameter/2, $seat->y-$seat->diameter/2+4), // +2 is for half of the font height
        'width'     => $seat->diameter,
      );
    }
    
    if ( sfConfig::get('sf_web_debug', false) && $request->hasParameter('debug') )
      return 'Success';
    return 'Json';
