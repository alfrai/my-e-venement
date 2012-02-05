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
  $tickets = array();
  
  while ( $line = fgetcsv($fp, 0, ';') )
  if ( floatval($line[23]) > 0 )
  {
    $ticket = array();
    $ticket['name']       = $line[10];
    $ticket['firstname']  = $line[11];
    $ticket['postalcode'] = $line[12];
    $ticket['city']       = '';
    $ticket['country']    = $line[13];
    $ticket['cancel']     = $line[1] == 'V' ? false : true;
    $ticket['price_name'] = $line[5];
    $ticket['price_id']   = 35; // TODO
    $ticket['value']      = $line[23];
    //$ticket['id']       = $line[24]; // TODO
    
    // created_at
    $ticket['created_at'] = explode(' ',$line[2]);
    $ticket['created_at'][0] = explode('/',$ticket['created_at'][0]);
    $ticket['created_at'][0] = array_reverse($ticket['created_at'][0]);
    $ticket['created_at'][0] = implode('-',$ticket['created_at'][0]);
    print_r($ticket['created_at'][0]);
    $ticket['created_at'] = implode(' ',$ticket['created_at']);
    
    $tickets[] = $ticket;
  }
