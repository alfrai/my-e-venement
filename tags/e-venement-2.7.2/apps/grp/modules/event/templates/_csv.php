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
*    Copyright (c) 2006-2012 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2012 Libre Informatique [http://www.libre-informatique.fr/]
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
  $vars['options']['header'] = array(
    'event'         => __('Event'),
    'date'          => __('Date'),
    'organism'      => __('Organism'),
    'organism_an'   => __('Admin. ID'),
    'organism_phones' => __('Phonenumbers'),
    'organism_email'  => __('Email address'),
    'organism_groups' => __('Remarkable'),
    'contact'       => __('Contact'),
    'groups'        => __('Remarkable'),
    'professional'  => __('Function'),
    'professional_phonenumber' => __('Phonenumber'),
    'professional_email'  => __('Contact email'),
    'professional_groups' => __('Remarkable'),
    'function'      => __('Fonction'),
    'department'    => __('Department'),
    'address'       => __('Address'),
    'postalcode'    => __('Postalcode'),
    'city'          => __('City'),
    'country'       => __('Country'),
  );
  foreach ( $prices as $id => $price )
    $vars['options']['header'][$id] = $price;
  $vars['options']['header']['total'] = __('Total');
  
  include_partial('global/csv',$vars);
