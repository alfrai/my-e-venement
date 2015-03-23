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

$separator = ';';
$tarif_line = 'Type de tarif';
$client_line = 'Categorie Client';
$zone_line = 'Genre de Zone';
$category_line = 'Categorie Client';
$charset = sfConfig::get('software_internals_charset');
$tarifs = $categories = $workspaces = array();

for ( $i = 0 ; $line = fgetcsv($fp, 0, $separator) ; $i++ )
{
  // creation of prices database
  if ( $line[0] === $tarif_line || $i === $tarif_line )
  {
    if ( is_int($tarif_line) && trim($line[0]) == '' || !is_int($tarif_line) )
    if ( isset($line[1]) && isset($line[2]) )
    {
      $tarifs[$line[2]] = iconv($charset['ms'],$charset['db'],$line[1]);
      if ( !is_int($tarif_line) )
        $tarif_line = $i+1;
      if ( trim($line[0]) == '' && is_int($tarif_line) )
        $tarif_line++;
    }
  }
  
  // creation of types of clients database
  if ( $line[0] === $client_line || $i === $client_line )
  {
    if ( is_int($client_line) && trim($line[0]) == '' || !is_int($client_line) )
    if ( isset($line[1]) && isset($line[2]) )
    {
      $clients[$line[2]] = iconv($charset['ms'],$charset['db'],$line[1]);
      if ( !is_int($client_line) )
        $client_line = $i+1;
      if ( trim($line[0]) == '' && is_int($client_line) )
        $client_line++;
    }
  }
  
  // creation of workspaces database
  if ( $line[0] === $zone_line || $i === $zone_line )
  {
    if ( is_int($zone_line) && trim($line[0]) == '' || !is_int($zone_line) )
    if ( isset($line[1]) && isset($line[2]) )
    {
      $workspaces[$line[2]] = iconv($charset['ms'],$charset['db'],$line[1]);
      if ( !is_int($zone_line) )
        $zone_line = $i+1;
      if ( trim($line[0]) == '' && is_int($zone_line) )
        $zone_line++;
    }
  }
  
  // creation of tickets to integrate
  if ( isset($line[23]) && floatval($line[23]) > 0 )
  {
    $ticket = array();
    $ticket['name']       = iconv($charset['ms'],$charset['db'],$line[10]);
    $ticket['firstname']  = iconv($charset['ms'],$charset['db'],$line[11]);
    $ticket['postalcode'] = iconv($charset['ms'],$charset['db'],$line[12]);
    $ticket['city']       = '';
    $ticket['country']    = iconv($charset['ms'],$charset['db'],$line[13]);
    $ticket['cancel']     = $line[1] == 'V' ? false : true;
    $ticket['price_name'] = $tarifs[$line[5]].'/'.$clients[$line[6]];
    $ticket['price_id']   = isset($this->translation['prices'][$ticket['price_name']]) ? $this->translation['prices'][$ticket['price_name']]['id'] : NULL;
    $ticket['workspace_id']=isset($this->translation['workspaces'][$workspaces[$line[8]]]) ? $this->translation['workspaces'][$workspaces[$line[8]]] : NULL;
    $ticket['value']      = isset($this->translation['prices'][$ticket['price_name']]) ? $this->translation['prices'][$ticket['price_name']]['value'] : $line[23];
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
