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
  // returns auth key
  function getKey($login,$pass,$salt)
  {
    return md5($login.md5($pass).$salt);
  }
  
  function verifyChecksum($mixed,$salt = '')
  {
    if ( is_array($mixed) )
      $arr = $mixed;
    else
      $arr = jsonToArray($mixed);
    $checksum = $arr['checksum'];
    unset($arr['checksum']);
    return $checksum == md5(json_encode($arr).$salt);
  }
  function freeChecksum($mixed)
  {
    if ( is_array($mixed) )
      $arr = $mixed;
    else
      $arr = jsonToArray($mixed);
    unset($arr['checksum']);
    return is_array($mixed) ? $arr : json_encode($arr);
  }
  function addChecksum($mixed,$salt = '')
  {
    if ( is_array($mixed) )
    {
      $arr = $mixed;
      $json = json_encode($arr);
    }
    else
    {
      $json = $mixed;
      $arr = jsonToArray($json);
    }
    $arr['checksum'] = md5($json.$salt);
    return json_encode($arr);
  }
  function jsonToArray($json)
  {
    return json_decode($json,true);
  }
  
  function whatToPay($transaction)
  {
    global $bd;
    $query = 'SELECT sum(case when mt.prix IS NOT NULL then mt.prix else t.prix end) AS topay
              FROM transaction tr
              LEFT JOIN reservation_pre p ON tr.id = p.transaction AND NOT p.annul
              LEFT JOIN tarif t ON t.id = p.tarifid
              LEFT JOIN manifestation_tarifs mt ON mt.manifestationid = p.manifid AND mt.tarifid = t.id
              WHERE tr.id = '.$transaction;
    $request = new bdRequest($bd,$query);
    $topay = $request->getRecord('topay');
    $request->free();
    
    return $topay;
  }
?>
