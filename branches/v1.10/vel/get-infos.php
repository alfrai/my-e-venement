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
    * Retreiving all manifs informations sorted both by event and by site
    * GET params :
    *   - key : a string formed with md5(name + password + salt) (required)
    *   - manifs[]: multiple possible manifestation.id if a focus is wanted (optionnal)
    * Returns :
    *   - HTTP return code
    *     . 200 if all will be processed normally
    *     . 403 if authentication as a valid webservice has failed
    *     . 500 if there was a problem processing the demand
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
    $nav->httpStatus(403);
    die();
  }
  
  $debug = isset($_GET['debug']);
  
  $nav->mimeType($debug ? 'text/plain' : 'application/json');
  
  $fields = array(
    'eventid'   => 'e.id',
    'event'     => 'e.nom',
    'description' => 'e.description',
    'ages'      => 'e.ages',
    'manifid'   => 'm.id',
    'date'      => 'm.date',
    'jauge'     => 'm.jauge',
    'siteid'    => 's.id',
    'sitename'  => 's.nom',
    'siteaddr'  => 's.adresse',
    'sitezip'   => 's.cp',
    'sitecity'  => 's.ville',
    'sitecountry'=> 's.pays',
    'tarif'     => 't.key',
    'tarifdesc' => 't.description',
    'price'     => 't.prix',
  );
  
  $select = array();
  foreach ( $fields as $key => $value )
    $select[] = $value.' AS '.$key;
  
  $manifs = array();
  if ( is_array($_GET['manifs']) )
  foreach ( $_GET['manifs'] as $manif )
  if ( intval($manif) > 0 )
    $manifs[] = intval($manif);
  
  $config['vel']['min-tickets'] = intval($config['vel']['min-tickets']);
  $still_have = 'm.jauge - sum((c.id IS NOT NULL)::integer) + sum((c.id IS NOT NULL AND p.annul)::integer)*2 - sum((bdc.id IS NOT NULL AND c.id IS NULL)::integer) - sum((cont.id IS NOT NULL)::integer)';
  $where = array(
    'm.id IS NOT NULL',
    'm.vel',
    'm.date > now()',
  );
  if ( count($manifs) > 0 )
    $where[] = 'm.id IN ('.implode(',',$manifs).')';
  $subq  = 'SELECT manifid, key, description, (case when mt.prix IS NOT NULL then mt.prix else t.prix end) AS prix
            FROM (SELECT manif.id AS manifid, t.*
                  FROM tarif t, manifestation manif
                  WHERE (t.date,t.key) IN (SELECT max(date),key FROM tarif GROUP BY key)
                    AND NOT t.desact
                    AND t.vel
            ) AS t
            LEFT JOIN manifestation_tarifs mt ON mt.manifestationid = t.manifid AND t.key = (SELECT key FROM tarif WHERE id = mt.tarifid)';
  $query = 'SELECT '.implode(',',$select).',
                   (SELECT min(date) FROM manifestation WHERE evtid = e.id) AS date_max,
                   (SELECT max(date) FROM manifestation WHERE evtid = e.id) AS date_min,
                   (case when '.$still_have.' > '.$config['vel']['min-tickets'].' then '.($config['vel']['min-tickets']+1).' when '.$still_have.' <= 0 then 0 else '.$still_have.' end) AS still_have
            FROM evenement e
            LEFT JOIN manifestation m ON e.id = m.evtid
            LEFT JOIN site s ON s.id = m.siteid
            LEFT JOIN reservation_pre p ON p.manifid = m.id
            LEFT JOIN reservation_cur c ON c.resa_preid = p.id AND NOT c.canceled
            LEFT JOIN bdc ON bdc.transaction = p.transaction
            LEFT JOIN contingeant cont ON cont.transaction = p.transaction
            LEFT JOIN ('.$subq.') AS t ON t.manifid = m.id
            WHERE '.implode(' AND ',$where).'
            GROUP BY '.implode(',',$fields).'
            ORDER BY e.nom, m.date, s.nom, t.key';
  $request = new arrayBdRequest($bd,$query);
  if ( $debug )
    echo $query;
  
  if ( $request->hasFailed() )
  {
    $nav->httpStatus(500);
    die($debug ? $query : '');
  }
  
  $arr = array();
  while ( $rec = $request->getRecordNext() )
  if ( $config['vel']['show-full-manifs'] || $rec['still_have'] > 0 )
  {
    $arr['events'][$rec['eventid']]['id']       = $rec['eventid'];
    $arr['events'][$rec['eventid']]['name']     = $rec['event'];
    $arr['events'][$rec['eventid']]['ages']     = $rec['ages'];
    $arr['events'][$rec['eventid']]['description'] = $rec['description'];
    $arr['events'][$rec['eventid']]['date']['min'] = $rec['date_max'];
    $arr['events'][$rec['eventid']]['date']['max'] = $rec['date_min'];
    
    $arr['sites'][$rec['siteid']]['id']         = $rec['siteid'];
    $arr['sites'][$rec['siteid']]['name']       = $rec['sitename'];
    $arr['sites'][$rec['siteid']]['address']    = $rec['siteaddr'];
    $arr['sites'][$rec['siteid']]['postal']     = $rec['sitezip'];
    $arr['sites'][$rec['siteid']]['city']       = $rec['sitecity'];
    $arr['sites'][$rec['siteid']]['country']    = $rec['sitecountry'];
    
    $tarif = array(
      'name'  => $rec['tarif'],
      'desc'  => $rec['tarifdesc'],
      'price' => $rec['price'],
    );
    $rec['tarifs'] = array($tarif['name'] => $tarif);
    
    unset($rec['tarif'],$rec['tarifdesc'],$rec['prix'],$rec['description']);
    unset($rec['date_max'],$rec['date_min']);

    if ( !is_array($arr['events'][$rec['eventid']][$rec['manifid']]) )
    {
      $arr['events'][$rec['eventid']][$rec['manifid']] =
      $arr['sites'] [$rec['siteid']] [$rec['manifid']] = $rec;
    }
    else
    {
      $arr['events'][$rec['eventid']][$rec['manifid']]['tarifs'][$tarif['name']] = 
      $arr['sites'] [$rec['siteid']] [$rec['manifid']]['tarifs'][$tarif['name']] = $tarif;
    }
  }
  $request->free();
  
  $nav->httpStatus(200);
  if ( $debug ) print_r($arr);
  echo $json = addChecksum($arr,$salt);
?>

