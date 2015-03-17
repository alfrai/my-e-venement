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
    if ( !($this->getRoute() instanceof sfObjectRoute) )
      return $this->redirect('ticket/sell');
    
    //$this->transaction = $this->getRoute()->getObject();
    $q = Doctrine::getTable('Transaction')
      ->createQuery('t')
      ->andWhere('t.id = ?',$request->getParameter('id'))
      ->leftJoin('m.Location l')
      ->leftJoin('m.Organizers o')
      ->leftJoin('m.Event e')
      ->leftJoin('e.MetaEvent me')
      ->leftJoin('e.Companies c')
      ->orderBy('m.happens_at, tck.price_name, tck.id')
      ->andWhere('tck.id NOT IN (SELECT tck2.duplicating FROM Ticket tck2 WHERE tck2.duplicating IS NOT NULL)')
      ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL')
      ->andWhere('tck.price_id IS NOT NULL')
    ;
    if ( $request->hasParameter('toprint') )
    {
      $tids = $request->getParameter('toprint');
      
      if ( !is_array($tids) ) $tickets = array($tids);
      foreach ( $tids as $key => $value )
        $tids[$key] = intval($value);
      
      $q->andWhereIn('tck.id',$tids);
    }
    $this->transaction = $q->fetchOne();
    
    // if any ticket needs a seat, do what's needed
    $this->redirectToSeatsAllocationIfNeeded('integrate');
    
    $this->tickets = array();
    foreach ( $this->transaction->Tickets as $ticket )
    if ( !$ticket->printed_at && !$ticket->integrated_at )
    {
      $ticket->integrated_at = date('Y-m-d H:i:s');
      $ticket->save();
      $this->tickets[] = $ticket;
    }
    
    if ( count($this->tickets) <= 0 )
      $this->setTemplate('close');
