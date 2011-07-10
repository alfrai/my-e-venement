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
  if ( !$options['noheader'] )
  {
    if ( !isset($options['header']) )
    {
      $line = array(
        'title'     => __('Title'),
        'name'      => __('Name'),
        'firstname' => __('Firstname'),
        'address'   => __('Address'),
        'postalcode'=> __('Postalcode'),
        'city'      => __('City'),
        'country'   => __('Country'),
        'npai'      => __('Npai'),
        'email'     => __('email'),
        'description' => __('Keywords'),
        'phonename'   => __('Phonetype'),
        'phonenumber' => __('Phonenumber'),
        'organism_category'   => __('Category of organism'),
        'organism_name'       => __('Organism'),
        'professional_department' => __('Department'),
        'professional_number' => __('Professional phone'),
        'professional_email'  => __('Professional email'),
        'professional_type_name' => __('Type of function'),
        'professional_name'   => __('Function'),
        'organism_address'    => __('Address'),
        'organism_postalcode' => __('Postalcode'),
        'organism_city'       => __('City'),
        'organism_country'    => __('Country'),
        'organism_email'      => __('Email'),
        'organism_url'        => __('URL'),
        'organism_npai'       => __('Npai'),
        'organism_description'=> __('Description'),
        'organism_phonename'  => __('Phonetype'),
        'organism_phonenumber'=> __('Phonenumber'),
        'information'         => __('Informations'),
      );
    }
    else
      $line = $options['header'];
    
    if ( count($options['fields']) > 0 )
    {
      $arr = array();
      foreach ( $options['fields'] as $field )
        $arr[$field] = $line[$field];
      $line = $arr;
    }
    
    if ( $options['ms'] )
    foreach ( $line as $key => $value )
      $line[$key] = iconv($charset['db'], $charset['ms'], $value);
    
    fputcsv($outstream, $line, $delimiter, $enclosure);
    ob_flush();
  }
