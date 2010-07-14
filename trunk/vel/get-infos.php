<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    beta-libs is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with beta-libs; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
  /**
    * Retreiving all events and manifs information
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    *   - manifs[]: multiple possible manifestation.id if a focus is wanted (optionnal)
    * Returns :
    *   - nothing : error
    *   - json: returns a json array describing all the necessary information
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  // auth
  if ( !$auth )
  {
    $nav->httpStatus(401);
    die();
  }
  
  $nav->mimeType('application/json');
  $nav->mimeType('text/plain');
  
  $fields = array(
    'eventid'   => 'e.id',
    'event'     => 'e.nom',
    'manifid'   => 'm.id',
    'date'      => 'm.date',
    'jauge'     => 'm.jauge',
    'sitenom'   => 's.nom',
    'siteadr'   => 's.adresse',
    'sitecp'    => 's.cp',
    'siteville' => 's.ville',
    'sitepays'  => 's.pays',
    'tarif'     => 't.key',
    'tarifdesc' => 't.description',
    'prix'      => 't.prix',
  );
  
  $select = array();
  foreach ( $fields as $key => $value )
    $select[] = $value.' AS '.$key;
  
  $manifs = array();
  if ( is_array($_GET['manifs']) )
  foreach ( $_GET['manifs'] as $manif )
  if ( intval($manif) > 0 )
    $manifs[] = intval($manif);
  
  $still_have = 'm.jauge - count(c.id) - sum((bdc.id IS NOT NULL AND c.id IS NULL)::integer)';
  if ( count($manifs) > 0 )
  $where = 'WHERE m.id IN ('.implode(',',$manifs).')';
  $subq  = 'SELECT manifid, key, description, (case when mt.prix IS NOT NULL then mt.prix else t.prix end) AS prix
            FROM (SELECT manif.id AS manifid, t.*
                  FROM tarif t, manifestation manif
                  WHERE (t.date,t.key) IN (SELECT max(date),key FROM tarif GROUP BY key)
                    AND NOT desact
                    AND vel
            ) AS t
            LEFT JOIN manifestation_tarifs mt ON mt.manifestationid = t.manifid AND t.key = (SELECT key FROM tarif WHERE id = mt.tarifid)';
  $query = 'SELECT '.implode(',',$select).',
                   (case when '.$still_have.' > 10 then 11 when '.$still_have.' <= 0 then 0 else '.$still_have.' end) AS still_have
            FROM evenement e
            LEFT JOIN manifestation m ON e.id = m.evtid
            LEFT JOIN site s ON s.id = m.siteid
            LEFT JOIN reservation_pre p ON p.manifid = m.id
            LEFT JOIN reservation_cur c ON c.resa_preid = p.id AND NOT c.canceled
            LEFT JOIN bdc ON bdc.transaction = p.transaction
            LEFT JOIN ('.$subq.') AS t ON t.manifid = m.id
            '.$where.'
            GROUP BY '.implode(',',$fields).'
            ORDER BY e.nom, m.date, s.nom, t.key';
  $request = new bdRequest($bd,$query);
  //echo $query;
  
  $arr = array();
  while ( $rec = $request->getRecordNext() )
    $arr[] = $rec;
  $request->free();
  
  //print_r($arr);
  echo $json = addChecksum($arr,$salt);
?>

