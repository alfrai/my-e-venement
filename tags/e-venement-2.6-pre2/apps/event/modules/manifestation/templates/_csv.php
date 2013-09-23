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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  $vars = array(
    'options',
    'delimiter',
    'enclosure',
    'outstream',
    'charset',
    'lines',
  );
  foreach ( $vars as $key => $value )
  {
    $vars[$value] = $$value;
    unset($vars[$key]);
  }
  $vars['options']['header'] = array_merge(array(
    'contact'       => __('Contact'),
    'organism'      => __('Organism'),
    'department'    => __('Department'),
    'organism_an'   => __('Admin. ID'),
  ),$prices,array(
    'total_qty'     => __('Total'),
    'total_value'   => __('Value'),
    'accounting'    => __('Acc.'),
    'transaction'   => __('Transaction'),
  ));
  foreach ( $prices as $id => $price )
    $vars['options']['header'][$id] = $price;
  
  include_partial('global/csv',$vars);
