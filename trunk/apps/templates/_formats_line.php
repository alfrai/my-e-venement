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
  $separator = false;
  foreach ( $fields_description as $name => $desc )
  if ( $line[$name] && is_array($desc) )
  {
    if ( $separator )
      echo $meta_format['separator'];
    
    $field = $line[$name];
    
    switch ( substr($field,0,1) ) {
    case '.':
      $value = $object;
      $arr = explode('.',substr($field,1));
      foreach ( $arr as $prop )
        $value = $value->$prop;
      
      // subobject's property
      include_partial('global/formats_field',array(
        'name'              => $name,
        'value'             => $value,
        'field_description' => isset($fields_description[$name]) && is_array($fields_description[$name])
          ? $fields_description[$name]
          : array(),
        'meta_format'       => $meta_format,
      ));
      break;
    case '_':
      // arbitrary value
      include_partial(substr($field,1),array(
        'name'              => $name,
        'object'            => $object,
        'field_description' => isset($fields_description[$name]) && is_array($fields_description[$name])
          ? $fields_description[$name]
          : array(),
        'meta_format'       => $meta_format,
        'criterias'         => $criterias,
      ));
      break;
    case '~':
      // pre-recorded value
      $fdesc = $fields_description[$name];
      if ( !isset($extrafields[substr($field,1)])
        ||  isset($extrafields[substr($field,1)]) && !in_array($fdesc['type'], array('integer','float')) )
      {
        if ( $fdesc['type'] == 'date' )
          $fdesc['size'] = strlen(date($fdesc['format']));
        $fdesc['type'] = 'string';
      }
      
      include_partial('global/formats_field',array(
        'name'              => $name,
        'value'             => isset($extrafields[substr($field,1)]) ? $extrafields[substr($field,1)] : '',
        'field_description' => $fdesc,
        'meta_format'       => $meta_format,
      ));
      break;
    default:
      // direct object's attribute
      include_partial('global/formats_field',array(
        'name'              => $name,
        'value'             => $object->$field,
        'field_description' => isset($fields_description[$name]) && is_array($fields_description[$name])
          ? $fields_description[$name]
          : array(),
        'meta_format'       => $meta_format,
      ));
      break;
    }
    $separator = true;
  }
