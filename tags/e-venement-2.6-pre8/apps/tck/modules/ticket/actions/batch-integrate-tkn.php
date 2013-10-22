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
$charset = sfConfig::get('software_internals_charset');

for ( $i = 0 ; $line = fgets($fp) ; $i++ )
// if !EOF and !BOF
if ( strlen($line) == 216 || strlen($line) == 108 )
{
  $line = iconv($charset['old'],$charset['db'],$line);
  
  $tck['libelle']   = trim(substr($line,0,10));
  $tck['datetime']  = trim(substr($line,10,16));
  $tck['cancel']    = substr($line,26,1) == 'A' ? true : false;
  $tck['category']  = trim(substr($line,27,2));
  $tck['workspace_id']=isset($this->translation['workspaces'][$cat = $tck['category']]) ? $this->translation['workspaces'][$cat] : NULL;
  $tck['price_name']= $tck['category'].trim(substr($line,29,10));
  $tck['price_id']  = isset($this->translation['prices'][$tck['price_name']]) ? $this->translation['prices'][$tck['price_name']]['id'] : NULL;
  $tck['fiscal']    = trim(substr($line,39,10));
  $tck['zone']      = trim(substr($line,49,4));
  $tck['rank']      = trim(substr($line,53,4));

  $tck['seat']      = trim(substr($line,57,4));
  $tck['value']     = isset($this->translation['prices'][$tck['price_name']]) ? $this->translation['prices'][$tck['price_name']]['value'] : trim(substr($line,61,10));
  $tck['devise']    = trim(substr($line,71,3));
  $tck['id']        = trim(substr($line,74,16));
  $tck['name']      = trim(substr($line,106,32));
  $tck['firstname'] = trim(substr($line,138,32));
  $tck['city']      = trim(substr($line,170,25));
  $tck['postalcode']= trim(substr($line,195,5));
  $tck['country']   = trim(substr($line,200,7));
  $tck['bank_id']   = trim(substr($line,207,6));
  $tck['type']      = 'tkn';
  $tck['created_at'] = date('Y-m-d H:i:s');
  
  if ( $tck['cancel'] )
    unset($tickets[$tck['id']]);
  else
    $tickets[$tck['id']] = $tck;
}
