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
      ->andWhere('tck.duplicate IS NULL')
      ->andWhere('tck.printed = ? AND tck.integrated = ?',array(false,false));
    $transactions = $q->execute();
    $this->transaction = $transactions[0];
    
    $this->tickets = array();
    foreach ( $this->transaction->Tickets as $ticket )
    {
      $ticket->integrated = true;
      $ticket->save();
      $this->tickets[] = $ticket;
    }
    
    if ( count($this->tickets) <= 0 )
      $this->setTemplate('close');
