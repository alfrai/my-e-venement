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
  $this->debug($request);
  if ( !$request->getParameter('manifestation_id', false) )
    throw new liOnlineSaleException('To access named tickets, you need a manifestation_id parameter. None given');
  
  $q = Doctrine::getTable('Ticket')->createQuery('tck')
    ->andWhere('tck.manifestation_id = ?', $request->getParameter('manifestation_id'))
    ->andWhere('tck.transaction_id = ?', $this->getUser()->getTransactionId())
    ->andWhere('tck.printed_at IS NULL')
    ->andWhere('tck.integrated_at IS NULL')
    ->andWhere('tck.cancelling IS NULL')
    ->andWhere('tck.duplicating IS NULL')
    ->andWhere('tck.price_id IS NOT NULL')
    
    ->leftJoin('tck.Seat s')
    ->leftJoin('tck.DirectContact c')
    ->leftJoin('tck.Gauge g')
    ->leftJoin('g.Workspace ws')
    ->leftJoin('tck.Price p')
    
    ->leftJoin('tck.Transaction t')
    ->leftJoin('t.Order o')
    ->andWhere('o.id IS NULL')
    
    ->orderBy('ws.name, p.name, tck.value')
  ;
  $tickets = $q->execute();
  
  // POST data
  $data = $request->getParameter('ticket');
  if ( isset($data['%%ticket_id%%']) )
    unset($data['%%ticket_id%%']);
  
  $this->data = array();
  foreach ( $tickets as $ticket )
  {
    // the DB data
    if ( isset($data[$ticket->id]) )
    {
      if ( isset($data[$ticket->id]['comment']) && $ticket->comment != $data[$ticket->id]['comment'] )
      {
        $ticket->comment = $data[$ticket->id]['comment'];
        $ticket->save();
      }
      
      foreach ( array('name', 'firstname', 'email') as $field )
      if (!( isset($data[$ticket->id]['contact'][$field]) && $data[$ticket->id]['contact'][$field] ))
      {
        unset($ticket->DirectContact);
        $ticket->save();
      }
      elseif ( $ticket->DirectContact->$field != $data[$ticket->id]['contact'][$field] )
      {
        if ( !$ticket->contact_id )
          $ticket->DirectContact = new Contact;
        
        // the contact has at least a name
        if ( $data[$ticket->id]['contact']['name'] )
        {
          // if the last contact was already confirmed, cannot modify such a contact
          if ( $ticket->DirectContact->confirmed )
          {
            $ticket->DirectContact = new Contact;
            $ticket->DirectContact->confirmed = false;
          }
          
          foreach ( array('name', 'firstname', 'email') as $field )
            $ticket->DirectContact->$field = $data[$ticket->id]['contact'][$field];
          
          $validator = new sfValidatorEmail;
          try {
            $ticket->DirectContact->email = $validator->clean($ticket->DirectContact->email);
            $ticket->save();
          }
          catch ( sfValidatorError $e ) { }
        }
        else
        {
          if ( $ticket->DirectContact->confirmed )
            $ticket->contact_id = NULL;
          else
            $ticket->DirectContact->delete();
        }
        
        break;
      }
    }
    
    // the json data
    $this->data[] = array(
      'id' => $ticket->id,
      'seat_name'         => (string)$ticket->Seat,
      'seat_id'           => $ticket->seat_id,
      'price_name'        => (string)$ticket->Price,
      'price_id'          => $ticket->price_id,
      'gauge_name'        => (string)$ticket->Gauge,
      'gauge_id'          => $ticket->gauge_id,
      'contact_id'        => $ticket->contact_id,
      'contact_name'      => $ticket->contact_id ? $ticket->DirectContact->name : NULL,
      'contact_firstname' => $ticket->contact_id ? $ticket->DirectContact->firstname : NULL,
      'contact_email'     => $ticket->contact_id ? $ticket->DirectContact->email : NULL,
      'comment'           => $ticket->comment,
    );
  }
  
  return 'Success';
