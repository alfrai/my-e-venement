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

$tarif_line = 8;
$tarifs = array();
$charset = sfContext::getInstance()->getConfiguration()->charset;

for ( $i = 0 ; $line = fgetcsv($fp, 0, ';') ; $i++ )
{
  // creation of prices database
  if ( $i == $tarif_line )
  {
    $tarif_line++;
    $tarifs[$line[2]] = iconv($charset['ms'],'UTF-8',$line[1]);
    if ( !isset($line[1]) || !isset($line[2]) )
      $tarif_line = 8;
  }
  if ( isset($line[23]) && floatval($line[23]) > 0 )
  {
    $ticket = array();
    $ticket['name']       = $line[10];
    $ticket['firstname']  = $line[11];
    $ticket['postalcode'] = $line[12];
    $ticket['city']       = '';
    $ticket['country']    = $line[13];
    $ticket['cancel']     = $line[1] == 'V' ? false : true;
    $ticket['price_name'] = $tarifs[$line[5]];
    $ticket['price_id']   = $price_default_id;
    $ticket['value']      = $line[23];
    $ticket['id']         = $line[15];
    $ticket['type']       = 'fb';
    
    // created_at
    $ticket['created_at'] = explode(' ',$line[2]);
    $ticket['created_at'][0] = explode('/',$ticket['created_at'][0]);
    $ticket['created_at'][0] = array_reverse($ticket['created_at'][0]);
    $ticket['created_at'][0] = implode('-',$ticket['created_at'][0]);
    $ticket['created_at'] = implode(' ',$ticket['created_at']);
    
    $tickets[] = $ticket;
  }
}
