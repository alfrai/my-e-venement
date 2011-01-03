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
*
***********************************************************************************/
?>
<?php
  require 'config.php';
  
  includeClass('bd');
  includeClass('bdRequest');
  $bd2    = new bd ( $config["database"]["name"],
                     $config["database"]["server"],
                     $config["database"]["port"],
                     $config["database"]["user"],
                     $config["database"]["passwd"] );
  $bd     = new bd ( 'e_venement_2',
                     $config["database"]["server"],
                     $config["database"]["port"],
                     $config["database"]["user"],
                     $config["database"]["passwd"] );
  
  $query = ' SELECT * FROM organisme';
  $request = new bdRequest($bd2,$query);
  
  $conversion = array(
    'id'          => 'id',
    'nom'         => 'name',
    'creation'    => 'created_at',
    'modification'=> 'updated_at',
    'description' => 'description',
    'adresse'     => 'address',
    'cp'          => 'postalcode',
    'ville'       => 'city',
    'state'       => 'country',
    'email'       => 'email',
    'npai'        => 'npai',
    'url'         => 'url',
    'categorie'   => 'category',
  );
  
  $cpt = array();
  while ( $rec = $request->getRecordNext() )
  {
    print_r($rec);
    exit(0);
    /*
    $arr = array();
    foreach ( $conversion as $old => $new )
      $arr[$new] = $rec[$old];
    if ( $bd->addRecord('contact',$arr) !== false )
      $cpt['ok']++;
    else
      $cpt['ko']++;
    */
  }
  
  $request->free();
  $bd2->free();
  $bd->free();
  
  print_r($cpt);
?>
