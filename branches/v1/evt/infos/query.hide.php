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
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
    $select = array(
      'id', 'organisme1', 'organisme2', 'organisme3', 'nom', 'description',
      'categorie', 'typedesc', 'mscene', 'mscene_lbl', 'textede', 'textede_lbl', 'duree', 'ages', 'code', 'creation', 'modification', 'catdesc',
      'manifid', 'date', 'vel', 'manifdesc',
      'siteid', 'sitenom', 'ville', 'cp', 'plnum', 'deftva', 'txtva', 'colorname', 'color',
    );
    $sums = array('jauge','commandes','resas','preresas',);
    $buf = array();
    foreach ( $sums as $sum )
      $buf[] = 'sum('.$sum.') as '.$sum;
    $sums = $buf;
    $query  = " SELECT ".implode(',',$select).", ".implode(',',$sums)."
                FROM info_resa
                WHERE manifid = ".$manifid."
                  ".( $_GET['spaces'] != 'all' ? "AND spaceid ".($user->evtspace ? ' = '.$user->evtspace : 'IS NULL') : '')."
                GROUP BY ".implode(',',$select);
?>
