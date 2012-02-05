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
        while ( $line = fgetcsv($fp, 0, ';') )
        if ( floatval($line[23]) > 0 )
        {
          // if the line references a named contact
          if ( $line[10] && $line[11] && $line[12] )
          {
            $charset = sfContext::getInstance()->getConfiguration()->charset;
            $search = array(implode('* ',explode(' ',$line[10])).'*',implode('* ',explode(' ',$line[11])).'*');
            $search = strtolower(iconv($charset['db'],$charset['ascii'],implode(' ',$search)));
            $q = Doctrine::getTable('Contact')->createQuery('c')
              ->andWhere('c.postalcode = ?',$line[12]);
            $contacts = Doctrine::getTable('Contact')->search($search,$q)->execute();
            
            if ( $contacts->count() == 0 )
            {
              $transaction = new Transaction();
              $transaction->Contact = new Contact();
              $transaction->Contact->name = $line[10];
              $transaction->Contact->firstname = $line[11];
              $transaction->Contact->postalcode = $line[12];
              $postalcode = Doctrine::getTable('Postalcode')->createQuery()->andWhere('postalcode = ?',$line[12])->fetchOne();
              $transaction->Contact->city = $postalcode->city;
              $transaction->Contact->country = $line[13];
            }
            else
            {
              // keep the last transaction if contact is the same, or create a new one if not
              if ( $transaction->isNew()
                || $transaction->Contact instanceof Contact
                && $transaction->Contact->id != $contacts[0]->id )
              {
                $transaction = new Transaction();
                $transaction->Contact = $contacts[0];
              }
            }
          }
          else // if ( !($line[10] && $line[11] && $line[12]) )
          {
            if ( $transaction->Contact instanceof Contact )
            {
              $transaction = new Transaction();
              $transaction->Contact = NULL;
            }
          }
          
          // if it's not a cancellation
          if ( $line[1] == 'V' )
          {
            $ticket = new Ticket();
            $ticket->Manifestation = $this->manifestation;
            $ticket->price_name = $line[5];
            $ticket->price_id = 35;     // TODO
            $ticket->value = $line[23];
            $ticket->integrated = true;
            //$ticket->id = $line[15];  // TODO
            $ticket->created_at = date('Y-m-d H:i',strtotime($line[2]));
            
            $transaction->Tickets[] = $ticket;
          }
          
          $transaction->save();
        }
