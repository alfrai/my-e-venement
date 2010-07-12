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
    * Specifs...
    * GET params :
    *   - key : a string formed with md5(name + password + salt)
    * Returns :
    *   - nothing : error
    *   - json: returns a json array describing all the necessary information
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  if ( $user->evtlevel < $config["evt"]["right"]["view"] )
    die();
  
  $query = 'SELECT e.id eventid, e.nom event, m.id manifid, m.date, s.nom site, count(c.id) AS tickets
            FROM evenement e
            LEFT JOIN manifestation m ON e.id = m.evtid
            LEFT JOIN site s ON s.id = m.siteid
            LEFT JOIN reservation_pre p ON p.manifid = m.id
            LEFT JOIN reservation_cur c ON c.resa_preid = p.id AND NOT c.canceled
            GROUP BY e.id, e.nom, m.id, m.date, s.nom
            ORDER BY e.nom, m.date, s.nom';
  $request = new bdRequest($bd,$query);
  
  $arr = array();
  while ( $rec = $request->getRecordNext() )
    $arr[] = $rec;
  
  echo json_encode($arr);
  
  $request->free();
?>
