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
  use_helper('MultiByte');
  
  $decorator = isset($meta_format['decorator']) ? $meta_format['decorator'] : '';
  $value = (string) $value;
  
  if ( !is_array($field_description) )
    throw new liEvenementException('No field description given. ('.$name.')');
  
  if ( !isset($field_description['type']) )
    $field_description['type'] = '';
  
  switch ( $field_description['type'] ) {
  case 'integer':
    if (!( isset($field_description['size']) && intval($field_description['size']).'' === ''.$field_description['size']) )
      throw new liEvenementException('Invalid field_description format in formats.yml (integer)');
  
  $value = mb_str_pad($value, $field_description['size'], 0, STR_PAD_LEFT);
  break;
  
  case 'float':
    if (!( isset($field_description['size']) && intval($field_description['size']).'' === ''.$field_description['size'])
        && isset($field_description['toFixed']) && intval($field_description['toFixed']).'' === ''.$field_description['toFixed'] )
      throw new liEvenementException('Invalid field_description in format.yml (float)');
    
    $value = number_format(doubleval($value), $field_description['toFixed'], $field_description['dot'], '');
    $value = mb_str_pad($value , $field_description['size'], 0, STR_PAD_LEFT);
    break;
  
  case 'date':
    if (!isset($field_description['format']))
      throw new liEvenementException('Invalid field_description in format.yml (date)');
   
    if ( strtotime($value) > 0 )
      $value = date($field_description['format'],strtotime($value));
    else
      $value = mb_str_pad('',mb_strlen(date($field_description['format'])));
    break;
    
  default:
    if (!( isset($field_description['size']) && intval($field_description['size']).'' === ''.$field_description['size']) )
      throw new liEvenementException('Invalid field_description format in format.yml (default), with field '.$name.' of type '.$field_description['type']);
    
    $value = mb_str_pad($value, $field_description['size']);
    break;
  }
  
  // truncate the field at its maximum size
  if ( isset($field_description['size']) && mb_strlen($value) > $field_description['size'] )
    $value = mb_substr($value,0,$field_description['size']);
  
  // charset
  if ( isset($meta_format['charset']) )
  {
    $charset = sfconfig::get('software_internals_charset');
    $value = iconv($charset['db'],$meta_format['charset'],$value);
  }
  
  // echo'ing
  echo $decorator.iconv($charset['db'],$meta_format['charset'],$value).$decorator;
