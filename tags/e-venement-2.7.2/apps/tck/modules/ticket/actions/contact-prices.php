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
  
  // contact
  $cid = intval($request->getParameter('contact_id'));
  // transaction
  $tid = intval($request->getParameter('transaction_id'));
  // manifestation
  $mid = intval($request->getParameter('manifestation_id'));
  // price names
  $pn = $request->getParameter('price_names');
  if ( !is_array($pn) ) $pn = array($pn);
  foreach ( $pn as $key => $value )
    $pn[$key] = trim(strtolower($value));
  
  // check
  $q = Doctrine::getTable('Ticket')->createQuery('tck')
    ->select('tck.id')
    ->andWhere('tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL OR t.id IN (SELECT o.transaction_id FROM Order o)')
    ->andWhere('tck.duplicating IS NULL')
    ->andWhere('tck.cancelling IS NULL')
    ->andWhere('tck.id NOT IN (SELECT tck2.cancelling FROM Ticket tck2 WHERE tck2.cancelling IS NOT NULL)')
    ->leftJoin('tck.Manifestation m')
    ->leftJoin('m.Event e')
    ->leftJoin('e.MetaEvent me')
    ->leftJoin('tck.Transaction t')
    ->andWhere('t.id != ?',$tid)
    ->leftJoin('t.Contact c')
    ->leftJoin('tck.Price p')
    ->andWhereIn('LOWER(p.name)',$pn)
    ->andWhere('me.id IN (SELECT DISTINCT e2.meta_event_id FROM Transaction t2 LEFT JOIN t2.Tickets tck3 LEFT JOIN tck3.Manifestation m2 LEFT JOIN m2.Event e2 WHERE t2.id = ?)', $tid);
  $tck = $q->execute();
  $this->json = array('nb_tickets' => $tck->count());
  
  if ( $request->hasParameter('debug') )
  {
    return 'Success';
  }
  
  // disabling debug, because it's a webservice
  sfConfig::set('sf_web_debug', false);
  $this->getResponse()->setContentType('application/json');
  return $this->renderText(json_encode($this->json));

  
