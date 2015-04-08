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
  foreach ( $objects as $object )
  foreach ( $lines as $lname => $line )
  {
    if ( $lname == 'subobjects' )
      continue;
    
    // if looping in subobjects
    if ( $lname == 'loop' )
    {
      if ( !isset($line['subobjects']) )
        throw new liEvenementException('Missing the "subobjects" property, look into your format.yml file');
      
      // regularizing subobjects when it is a Doctrine_Collection or a single object
      if ( $object->get($line['subobjects']) instanceof Doctrine_Collection )
        $subobjects = $object->get($line['subobjects']);
      else
        $subobjects = array($object->get($line['subobjects']));
      
      // relaunching
      include_partial('global/formats',array(
        'lines' => $line,
        'objects' => $subobjects,
        'parent' => $object,
        'extrafields' => $extrafields,
        'fields_description' => $fields_description,
        'meta_format' => $meta_format,
        'criterias' => $criterias,
      ));
    }
    else
    {
      include_partial('global/formats_line',array(
        'line' => $line,
        'object' => $object,
        'extrafields' => $extrafields,
        'fields_description' => $fields_description,
        'meta_format' => $meta_format,
        'criterias' => $criterias,
      ));
      echo $meta_format['crlf'] == 'microsoft' ? "\r\n" : "\n";
    }
  }
