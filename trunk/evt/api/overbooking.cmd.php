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
    *   - transac : transaction's number, as given by the software
    * Returns :
    *   -   json: array containing manifestations overbooked (size == 0 if everything's ok)
    *   -   2 : error in the command calling... $_GET['transac'] not ok
    *   - 255 : the "jauge" is overbooked...
    *
    **/
?>
<?php
  require("conf.inc.php");
  
  $transac = intval($_GET['transac']);
  
  if ( $transac > 0 )
  {
    $query = 'SELECT manifid, jauge,
              (SELECT SUM(-(p.annul::integer*2-1)) AS printed
               FROM reservation_cur c, reservation_pre p
               WHERE p.id = c.resa_preid
                 AND NOT c.canceled
                 AND p.manifid = s.manifid
              )::integer AS printed,
              (SELECT SUM(-(p.annul::integer*2-1)) AS preresa
               FROM reservation_pre p
               WHERE p.id NOT IN (SELECT resa_preid FROM reservation_cur WHERE NOT canceled)
                 AND transaction != '.$transac.'
                 AND p.transaction IN (SELECT transaction FROM preselled)
                 AND p.manifid = s.manifid
              )::integer AS preselled,
              (SELECT SUM(-(p.annul::integer*2-1)) AS toprint
               FROM reservation_pre p
               WHERE id NOT IN (SELECT resa_preid FROM reservation_cur WHERE NOT canceled)
                 AND transaction = '.$transac.'
                 AND p.manifid = s.manifid
              )::integer AS toprint
               FROM ( SELECT DISTINCT p.manifid, m.jauge FROM reservation_pre p, manifestation m WHERE m.id = p.manifid AND transaction = '.$transac.' ) AS s';
    $request = new bdRequest($bd,$query);
    
    $r = array();
    while ( $chiffres = $request->getRecordNext() )
    if ( intval($chiffres['toprint']) > intval($chiffres['jauge']) - intval($chiffres['preselled']) - intval($chiffres['printed']) )
    {
      $r[] = $chiffres['manifid'];
    }
    
    echo json_encode($r);
    $request->free();
    $bd->free();
    if ( count($r) > 0 )
      die(255);
    else
      die(0);
  }
  
  $bd->free();
  echo 2;
  die(2);
?>
