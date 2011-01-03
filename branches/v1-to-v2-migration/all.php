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
  /**
    * usage: php all.php table1 [table2 [table3 ...]]
    * where: tableX is a table where import data to
    *
    **/

  require 'config.php';
  require 'functions.php';
  includeClass('bd');
  includeClass('bdRequest');
  
  $bd2    = new bd ( $config["database"]["name"],
                     $config["database"]["server"],
                     $config["database"]["port"],
                     $config["database"]["user"],
                     $config["database"]["passwd"] );
  $bd     = new bd ( $config["database2"]["name"],
                     $config["database2"]["server"],
                     $config["database2"]["port"],
                     $config["database2"]["user"],
                     $config["database2"]["passwd"] );
  
  $do = $_SERVER['argv'];
  unset($do[0]);
  
  $tables = array();
  
  // contact
  $from_table = 'personne';
  $to_table = 'contact';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $tables[] = $to_table;
    echo $to_table.' ';
    $conversion = array(
      'id'          => 'id',
      'prenom'      => 'firstname',
      'nom'         => 'name',
      'titre'       => 'title',
      'creation'    => 'created_at',
      'modification'=> 'updated_at',
      'description' => 'description',
      'password'    => 'password',
      'adresse'     => 'address',
      'cp'          => 'postalcode',
      'ville'       => 'city',
      'state'       => 'country',
      'email'       => 'email',
      'npai'        => 'npai',
    );
    $conversion = array_flip($conversion); // for lazzy workers
    $conversion['slug'] = NULL;
    $cpt = migrate($from_table,$conversion,$to_table);
    print_r($cpt);
  }
  
  // organism_category
  $from_table = 'org_categorie';
  $to_table = 'organism_category';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $tables[] = $to_table;
    echo $to_table.' ';
    $conversion = array(
      'id'          => 'id',
      'name'        => 'libelle',
      'created_at'  => NULL,
      'updated_at'  => NULL,
    );
    $conversion['slug'] = NULL;
    $cpt = migrate($from_table,$conversion,$to_table);
    print_r($cpt);
  }
  
  // organism
  $from_table = 'organisme';
  $to_table = 'organism';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
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
      'categorie'   => 'organism_category_id',
    );
    $conversion = array_flip($conversion); // for lazzy workers
    $conversion['slug'] = NULL;
    $tables[] = $to_table;
    echo $to_table.' ';
    $cpt = migrate($from_table,$conversion,$to_table);
    print_r($cpt);
  }
  
  // telephones contact
  $from_table = 'telephone';
  $to_table = 'contact_phonenumber';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'name' => 'type',
      'numero' => 'numero',
      'contact_id' => 'entiteid',
      'created_at'  => NULL,
      'updated_at'  => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,false);
    print_r($cpt);
  }
  
  // telephones contact
  $from_table = 'telephone';
  $to_table = 'organism_phonenumber';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'name' => 'type',
      'numero' => 'numero',
      'organism_id' => 'entiteid',
      'created_at'  => NULL,
      'updated_at'  => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,false);
    print_r($cpt);
  }
  
  // YOB
  $from_table = 'child';
  $to_table = 'y_o_b';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'year' => 'birth',
      'contact_id' => 'personneid',
      'created_at'  => NULL,
      'updated_at'  => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,false);
    print_r($cpt);
  }
  
  // professional_type
  $to_table = 'professional_type';
  $from_table = 'fonction';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'id'          => 'id',
      'name'        => 'libelle',
      'created_at'  => NULL,
      'updated_at'  => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,false);
    print_r($cpt);
  }
  
  // professional
  $to_table = 'professional';
  $from_table = 'org_personne';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'id'            => 'id',
      'organism_id'   => 'organismeid',
      'contact_id'    => 'personneid',
      'professional_type_id' => 'type',
      'name'          => 'fonction',
      'contact_number'=> 'telephone',
      'contact_email' => 'email',
      'department'    => 'service',
      'description'   => 'description',
      'created_at'    => NULL,
      'updated_at'    => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,false);
    print_r($cpt);
  }
  
  /*
  // group_table
  $to_table = 'group_table';
  $from_table = 'groupe';
  if ( in_array($to_table,$do) || count($do) == 0 )
  {
    $conversion = array(
      'id'            => 'id',
      'organism_id'   => 'organismeid',
      'contact_id'    => 'personneid',
      'professional_type_id' => 'type',
      'name'          => 'fonction',
      'contact_number'=> 'telephone',
      'contact_email' => 'email',
      'department'    => 'service',
      'description'   => 'description',
      'created_at'    => NULL,
      'updated_at'    => NULL,
    );
    echo $to_table.' ';
    $tables[] = $to_table;
    $cpt = migrate($from_table,$conversion,$to_table,false);
    print_r($cpt);
  }
  */
  
  
  print_r($tables);
  
  $bd->free();
  $bd2->free();
?>
