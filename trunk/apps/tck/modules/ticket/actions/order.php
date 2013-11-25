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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $this->executeAccounting($request,false);
    $this->order = $this->transaction->Order[0];
    
    if ( $request->hasParameter('cancel-order') )
    {
      $this->order->delete();
      foreach ( $this->transaction->Tickets as $ticket )
      {
        // updating tickets in bulk
        $q = Doctrine_Query::create()->from('Ticket tck')
          ->select('tck.id')
          ->andWhere('tck.transaction_id = ?',$this->transaction->id)
          ->andWhere('tck.numerotation IS NOT NULL OR tck.numerotation != ?', '')
          ->andWhere('tck.printed_at IS NOT NULL AND tck.integrated_at IS NOT NULL');
        $tickets = array();
        foreach ( $q->fetchArray() as $t )
          $tickets[] = $t['id'];
        
        $q->update()
          ->set('numerotation','NULL')
          ->set('updated_at', 'NOW()')
          ->set('version', 'version + 1')
          ->set('sf_guard_user_id',$this->getUser()->getId())
          ->execute();
        
        // tickets version
        if ( $tickets )
        {
          $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
          $query = 'INSERT INTO ticket_version SELECT * FROM ticket WHERE id IN ('.implode(',',$tickets).')';
          $stmt = $pdo->prepare($query);
          $stmt->execute();
        }
      }
      
      return sfView::NONE;
    }
    
    // saving the order, transforms common tickets into booked tickets
    if ( is_null($this->order->id) )
      $this->order->save();

    // if any ticket needs a seat, do what's needed
    $this->redirectToSeatsAllocationIfNeeded('order');
    
    // if everything's ok, prints out the order
    return 'Success';
